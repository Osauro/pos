<?php
/**
 * deploy.php — Auto-actualización para pos.misocio.bo
 *
 * Webhook GitHub: Settings > Webhooks > Payload URL = https://pos.misocio.bo/deploy.php
 *                 Content type = application/json   |   Just the push event
 */

$phpBin      = '/usr/local/bin/php';
$projectRoot = '/home/misocio405/pos.misocio.bo';

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

echo "=== ACTUALIZACIÓN: " . date('Y-m-d H:i:s') . " ===\n\n";

// 1. Código
run("git -C {$projectRoot} fetch origin");
run("git -C {$projectRoot} reset --hard origin/master");
run("git -C {$projectRoot} clean -fd");

// 2. Dependencias
run("{$composer} install --no-dev --optimize-autoloader --no-interaction");

// 3. Migraciones
run("{$artisan} migrate --force");

// 4. Cachés
run("{$artisan} optimize:clear");
run("{$artisan} config:cache");
run("{$artisan} route:cache");
run("{$artisan} view:cache");

// 5. Permisos
run("chmod -R 775 {$projectRoot}/storage");
run("chmod -R 775 {$projectRoot}/bootstrap/cache");

echo "=== Actualización completada: " . date('Y-m-d H:i:s') . " ===\n";

