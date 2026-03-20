<?php
/**
 * deploy.php — Auto-actualización para pos.misocio.bo
 *
 * Uso: https://pos.misocio.bo/deploy.php
 * En GitHub: Settings > Webhooks > Payload URL = https://pos.misocio.bo/deploy.php
 *            Content type = application/json   |   Just the push event
 */

// ── Configuración ─────────────────────────────────────────────────────────────
$phpBin      = '/usr/local/bin/php';
$projectRoot = '/home/misocio405/pos.misocio.bo';   // raíz del proyecto Laravel

// ── Helpers ───────────────────────────────────────────────────────────────────
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

// ── Despliegue ────────────────────────────────────────────────────────────────
echo "=== Despliegue iniciado: " . date('Y-m-d H:i:s') . " ===\n\n";

// 1. Obtener últimos cambios
run("git -C {$projectRoot} fetch origin");
run("git -C {$projectRoot} reset --hard origin/master");
run("git -C {$projectRoot} clean -fd");

// 2. Dependencias PHP (solo producción, sin scripts)
run("{$phpBin} {$projectRoot}/composer.phar install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || " .
    "composer install --no-dev --optimize-autoloader --no-interaction --working-dir={$projectRoot}");

// 3. Migraciones
run("{$phpBin} {$projectRoot}/artisan migrate --force");

// 4. Enlace de storage (por si no existe)
run("{$phpBin} {$projectRoot}/artisan storage:link");

// 5. Limpiar y reconstruir cachés
run("{$phpBin} {$projectRoot}/artisan optimize:clear");
run("{$phpBin} {$projectRoot}/artisan config:cache");
run("{$phpBin} {$projectRoot}/artisan route:cache");
run("{$phpBin} {$projectRoot}/artisan view:cache");

// 6. Permisos de storage y bootstrap/cache
run("chmod -R 775 {$projectRoot}/storage");
run("chmod -R 775 {$projectRoot}/bootstrap/cache");

echo "=== Despliegue completado: " . date('Y-m-d H:i:s') . " ===\n";
