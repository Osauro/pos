<?php

header('Content-Type: text/plain; charset=utf-8');

$phpBin      = '/usr/local/bin/php';
$projectRoot = '/home/misocio405/MiSocio';
$publicHtml  = '/home/misocio405/public_html';

function run(string $cmd): void
{
    echo "$ {$cmd}\n";
    $output = [];
    $code   = 0;
    exec($cmd . ' 2>&1', $output, $code);
    echo (implode("\n", $output) ?: '(sin salida)') . "\n";
    if ($code !== 0) echo "[ERROR] Código de salida: {$code}\n";
    echo "\n";
}

echo "=== Despliegue: " . date('Y-m-d H:i:s') . " ===\n\n";

run("git -C {$projectRoot} fetch origin");
run("git -C {$projectRoot} reset --hard origin/master");
run("git -C {$projectRoot} clean -fd"); // eliminar archivos no rastreados
run("/usr/bin/uapi VersionControlDeployment create repository_root='{$projectRoot}'");
run("{$phpBin} {$projectRoot}/artisan migrate --force");
run("{$phpBin} {$projectRoot}/artisan optimize:clear"); // limpia config, route, view, event, cache todo
run("{$phpBin} {$projectRoot}/artisan config:cache");
run("{$phpBin} {$projectRoot}/artisan route:cache");

// Verificar encoding en vistas del servidor
echo "=== Verificación de encoding ===\n";
$check = shell_exec("grep -rl $'\\xc3\\x83\\|\\xc3\\x82' {$projectRoot}/resources/views/ 2>/dev/null");
echo $check ? "ARCHIVOS ROTOS:\n{$check}" : "Encoding OK en todas las vistas\n";
echo "\n";

// Eliminar symlink si existe y crear directorio real en public_html/storage
run("rm -f {$publicHtml}/storage");
run("mkdir -p {$publicHtml}/storage");
run("chmod -R 775 {$publicHtml}/storage");

// Mover imágenes existentes de storage/app/public a public_html/storage (si las hay)
run("rsync -a {$projectRoot}/storage/app/public/ {$publicHtml}/storage/");

// Copiar .htaccess a public_html (contiene SymLinksIfOwnerMatch)
run("cp {$projectRoot}/public/.htaccess {$publicHtml}/.htaccess");

echo "=== Finalizado: " . date('Y-m-d H:i:s') . " ===\n";
