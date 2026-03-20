<?php
/**
 * deploy.php — Instalación inicial y auto-actualización para pos.misocio.bo
 *
 * Primera vez:    https://pos.misocio.bo/deploy.php
 * Webhook GitHub: Settings > Webhooks > Payload URL = https://pos.misocio.bo/deploy.php
 *                 Content type = application/json   |   Just the push event
 *
 * Si no existe vendor/ → instalación completa desde cero.
 * Si ya existe vendor/ → actualización normal.
 */

// ── Configuración ─────────────────────────────────────────────────────────────
$phpBin      = '/usr/local/bin/php';
$projectRoot = '/home/misocio405/pos.misocio.bo';

// ── Helper ────────────────────────────────────────────────────────────────────
header('Content-Type: text/plain; charset=utf-8');

function run(string $cmd): bool
{
    echo "$ {$cmd}\n";
    $output = [];
    $code   = 0;
    exec($cmd . ' 2>&1', $output, $code);
    echo (implode("\n", $output) ?: '(sin salida)') . "\n";
    if ($code !== 0) {
        echo "[ERROR] Código de salida: {$code}\n";
    }
    echo "\n";
    return $code === 0;
}

$composer = "composer --working-dir={$projectRoot}";
$artisan  = "{$phpBin} {$projectRoot}/artisan";
$esNuevo  = !is_dir("{$projectRoot}/vendor");

echo "=== " . ($esNuevo ? "INSTALACIÓN INICIAL" : "ACTUALIZACIÓN") . ": " . date('Y-m-d H:i:s') . " ===\n\n";

// ── 1. Código actualizado ─────────────────────────────────────────────────────
run("git -C {$projectRoot} fetch origin");
run("git -C {$projectRoot} reset --hard origin/master");
run("git -C {$projectRoot} clean -fd");

// ── 2. Dependencias Composer ──────────────────────────────────────────────────
run("{$composer} install --no-dev --optimize-autoloader --no-interaction");

// ── 3. Solo en instalación inicial ───────────────────────────────────────────
if ($esNuevo) {
    echo "--- Configuración inicial ---\n\n";

    // Crear .env si no existe
    if (!file_exists("{$projectRoot}/.env")) {
        run("cp {$projectRoot}/.env.example {$projectRoot}/.env");
        echo "⚠  Recuerda editar .env con los datos de tu base de datos.\n\n";
    }

    // Generar clave de app
    run("{$artisan} key:generate --force");
}

// ── 4. Migraciones ────────────────────────────────────────────────────────────
run("{$artisan} migrate --force");

// ── 5. Storage link ───────────────────────────────────────────────────────────
run("{$artisan} storage:link");

// ── 6. Cachés ─────────────────────────────────────────────────────────────────
run("{$artisan} optimize:clear");
run("{$artisan} config:cache");
run("{$artisan} route:cache");
run("{$artisan} view:cache");

// ── 7. Permisos ───────────────────────────────────────────────────────────────
run("chmod -R 775 {$projectRoot}/storage");
run("chmod -R 775 {$projectRoot}/bootstrap/cache");

echo "=== " . ($esNuevo ? "Instalación" : "Actualización") . " completada: " . date('Y-m-d H:i:s') . " ===\n";
if ($esNuevo) {
    echo "\n⚠  Edita el archivo .env en el servidor con los datos de tu base de datos\n";
    echo "   y luego vuelve a acceder a https://pos.misocio.bo/deploy.php\n";
}

