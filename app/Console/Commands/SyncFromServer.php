<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SyncFromServer extends Command
{
    protected $signature = 'app:sync-from-server
                            {--db-only     : Solo sincronizar base de datos}
                            {--images-only : Solo sincronizar imágenes}
                            {--tables=     : Tablas específicas separadas por coma (ej: productos,ventas)}
                            {--force       : No pedir confirmación}';

    protected $description = 'Importa tablas e imágenes del servidor remoto (DB1_*) al entorno local';

    /**
     * Tablas excluidas: son específicas de cada entorno y no deben sobreescribirse.
     */
    protected array $skipTables = [
        'migrations',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
        'personal_access_tokens',
    ];

    /**
     * Columnas de imagen por tabla.
     * Se descargan desde el storage remoto.
     */
    protected array $imageColumns = [
        'productos'        => ['imagen'],
        'users'            => ['imagen'],
        'tenant_user'      => ['qr_imagen'],
        'pagos_suscripcion'=> ['comprobante_path'],
        'galeria_imagenes' => ['url'],
    ];

    public function handle(): int
    {
        $dbOnly     = $this->option('db-only');
        $imagesOnly = $this->option('images-only');
        $force      = $this->option('force');
        $tablasFiltro = $this->option('tables')
            ? array_map('trim', explode(',', $this->option('tables')))
            : [];

        // ── Aviso de seguridad ────────────────────────────────────────────────
        if (!$imagesOnly && !$force) {
            $this->newLine();
            $this->line('<fg=red;options=bold>  ⚠  ADVERTENCIA</>');
            $this->line('  Esto SOBREESCRIBIRÁ la base de datos local con los datos del servidor remoto.');
            $this->line('  Base local   : <fg=yellow>' . config('database.connections.mysql.database') . '</>');
            $this->line('  Base remota  : <fg=yellow>' . config('database.connections.db1.database') . '</>');
            $this->newLine();

            if (!$this->confirm('  ¿Deseas continuar?')) {
                $this->info('  Operación cancelada.');
                return self::FAILURE;
            }
        }

        $inicio = now();

        if (!$imagesOnly) {
            $ok = $this->syncDatabase($tablasFiltro);
            if (!$ok) return self::FAILURE;
        }

        if (!$dbOnly) {
            $this->syncImages($tablasFiltro);
        }

        $this->newLine();
        $segundos = now()->diffInSeconds($inicio);
        $this->line("  ✅  <fg=green>Sincronización completada</> en {$segundos}s.");
        $this->newLine();

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────

    protected function syncDatabase(array $tablasFiltro): bool
    {
        $this->newLine();
        $this->line('<options=bold>━━━ BASE DE DATOS ━━━</options=bold>');

        // Verificar conexión remota
        try {
            DB::connection('db1')->getPdo();
            $this->line('  <fg=green>✓</> Conexión remota OK  (<fg=cyan>'
                . config('database.connections.db1.host') . '</>)');
        } catch (\Exception $e) {
            $this->error('  ✗ Sin conexión al servidor remoto: ' . $e->getMessage());
            return false;
        }

        $remoteDb = config('database.connections.db1.database');
        $local    = DB::connection('mysql');
        $remote   = DB::connection('db1');

        // Obtener listado de tablas del servidor remoto
        $rows   = $remote->select('SHOW TABLES');
        $colKey = "Tables_in_{$remoteDb}";
        $tablas = array_map(fn($r) => $r->$colKey, $rows);

        // Filtrar si el usuario especificó tablas concretas
        if (!empty($tablasFiltro)) {
            $tablas = array_filter($tablas, fn($t) => in_array($t, $tablasFiltro));
        }

        // Excluir tablas de sistema
        $tablas = array_values(array_filter($tablas, fn($t) => !in_array($t, $this->skipTables)));

        $this->line('  Tablas a sincronizar: <fg=yellow>' . count($tablas) . '</>');
        $this->newLine();

        $bar = $this->output->createProgressBar(count($tablas));
        $bar->setFormat('  %current%/%max% [%bar%] %percent:3s%%  %message%');
        $bar->setMessage('');
        $bar->start();

        $local->statement('SET FOREIGN_KEY_CHECKS=0');
        $errores = [];

        foreach ($tablas as $tabla) {
            $bar->setMessage($tabla);

            try {
                // Crear tabla local si no existe (copia la estructura del remoto)
                $existeLocal = $local->selectOne(
                    "SELECT COUNT(*) as cnt
                     FROM information_schema.tables
                     WHERE table_schema = DATABASE() AND table_name = ?",
                    [$tabla]
                )->cnt ?? 0;

                if (!$existeLocal) {
                    $createSql = $remote->select("SHOW CREATE TABLE `{$tabla}`");
                    $sql = $createSql[0]->{'Create Table'} ?? null;
                    if ($sql) {
                        $local->statement($sql);
                    }
                }

                // Vaciar tabla local
                $local->table($tabla)->truncate();

                // Determinar si la tabla tiene columna id (para chunkById)
                $columnas = array_column($remote->select("SHOW COLUMNS FROM `{$tabla}`"), 'Field');
                $tieneId  = in_array('id', $columnas);

                // Copiar en lotes de 500 filas
                $insertar = function ($rows) use ($local, $tabla) {
                    if ($rows->isEmpty()) return;
                    $local->table($tabla)->insert(
                        $rows->map(fn($r) => (array) $r)->all()
                    );
                };

                if ($tieneId) {
                    $remote->table($tabla)->chunkById(500, $insertar);
                } else {
                    $remote->table($tabla)->orderByRaw('1')->chunk(500, $insertar);
                }
            } catch (\Throwable $e) {
                $errores[] = "  • {$tabla}: " . $e->getMessage();
            }

            $bar->advance();
        }

        $local->statement('SET FOREIGN_KEY_CHECKS=1');
        $bar->finish();
        $this->newLine();

        if (!empty($errores)) {
            $this->newLine();
            $this->line('<fg=yellow>  Tablas con errores:</>');
            foreach ($errores as $msg) {
                $this->line("<fg=red>{$msg}</>");
            }
        }

        $this->line('  <fg=green>✓</> Base de datos sincronizada  ('
            . (count($tablas) - count($errores)) . '/' . count($tablas) . ' tablas OK)');

        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────

    protected function syncImages(array $tablasFiltro): void
    {
        $this->newLine();
        $this->line('<options=bold>━━━ IMÁGENES ━━━</options=bold>');

        $remoteUrl  = rtrim(env('DB1_APP_URL', ''), '/');
        $syncToken  = env('SYNC_TOKEN', '');

        if (empty($remoteUrl)) {
            $this->warn('  DB1_APP_URL no está configurada en .env. Saltando imágenes.');
            return;
        }
        if (empty($syncToken)) {
            $this->warn('  SYNC_TOKEN no está configurado en .env. Saltando imágenes.');
            return;
        }

        $this->line('  URL remota: <fg=cyan>' . $remoteUrl . '</>');

        // Recolectar todas las rutas de imagen desde el servidor remoto
        $rutas = collect();

        $columnasARevisar = empty($tablasFiltro)
            ? $this->imageColumns
            : array_intersect_key($this->imageColumns, array_flip($tablasFiltro));

        foreach ($columnasARevisar as $tabla => $cols) {
            foreach ($cols as $col) {
                try {
                    $vals = DB::connection('db1')
                        ->table($tabla)
                        ->whereNotNull($col)
                        ->where($col, '!=', '')
                        ->pluck($col);
                    $rutas = $rutas->merge($vals);
                } catch (\Throwable) {
                    // tabla o columna inexistente en remoto
                }
            }
        }

        $rutas = $rutas->filter()->unique()->values();

        if ($rutas->isEmpty()) {
            $this->line('  Sin imágenes que sincronizar.');
            return;
        }

        // ── Limpiar carpeta galeria antes de descargar ────────────────────────
        $galeriaEnRemoto = isset($columnasARevisar['galeria_imagenes']);
        if ($galeriaEnRemoto) {
            Storage::disk('public')->deleteDirectory('galeria');
            Storage::disk('public')->makeDirectory('galeria');
            $this->line('  <fg=yellow>↺</> Carpeta <fg=cyan>storage/app/public/galeria/</> vaciada.');
        }

        $this->line('  Imágenes encontradas: <fg=yellow>' . $rutas->count() . '</>');
        $this->newLine();

        $bar = $this->output->createProgressBar($rutas->count());
        $bar->setFormat('  %current%/%max% [%bar%] %percent:3s%%  %message%');
        $bar->setMessage('');
        $bar->start();

        $ok          = 0;
        $saltar      = 0;
        $err         = 0;
        $primerError = null;

        foreach ($rutas as $ruta) {
            $bar->setMessage(basename($ruta));

            // Imágenes de galería: siempre descargar (la carpeta fue limpiada)
            // Resto: saltar si ya existe localmente
            $esGaleria = str_starts_with(ltrim($ruta, '/'), 'galeria/');
            if (!$esGaleria && Storage::disk('public')->exists($ruta)) {
                $saltar++;
                $bar->advance();
                continue;
            }

            // Acceso directo a /storage/ (HTTP, sin SSL)
            $url = $remoteUrl . '/storage/' . ltrim($ruta, '/');

            try {
                $response = Http::timeout(30)
                    ->withoutVerifying()
                    ->get($url);

                if ($response->successful()) {
                    // Para galería la carpeta ya existe; para otras crearla si hace falta
                    if (!$esGaleria) {
                        Storage::disk('public')->makeDirectory(dirname($ruta));
                    }
                    Storage::disk('public')->put($ruta, $response->body());
                    $ok++;
                } else {
                    if ($primerError === null) {
                        $primerError = "HTTP {$response->status()} → {$url}";
                    }
                    $err++;
                }
            } catch (\Throwable $e) {
                if ($primerError === null) {
                    $primerError = $e->getMessage() . " → {$url}";
                }
                $err++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($primerError) {
            $this->newLine();
            $this->line('  <fg=red>Primer error detectado:</>');
            $this->line('  <fg=yellow>' . $primerError . '</>');
        }

        $this->line(sprintf(
            '  <fg=green>✓</> %d descargadas  •  %d ya existían  •  <fg=%s>%d errores</>',
            $ok,
            $saltar,
            $err > 0 ? 'red' : 'green',
            $err
        ));
    }
}
