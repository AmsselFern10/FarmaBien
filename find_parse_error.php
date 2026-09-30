<?php
// Detectar en qué archivos el git diff introdujo cambios para comparar con el error
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Intentar cargar TODAS las clases del app para encontrar ParseErrors
$dirs = [
    __DIR__ . '/app/Http/Controllers',
    __DIR__ . '/app/Models',
    __DIR__ . '/app/Services',
    __DIR__ . '/app/Http/Requests',
    __DIR__ . '/app/Providers',
];

$found = false;
foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($rii as $file) {
        if ($file->isDir() || $file->getExtension() !== 'php') continue;
        $path = $file->getPathname();
        $result = shell_exec("php -l " . escapeshellarg($path) . " 2>&1");
        if (strpos($result, 'No syntax errors') === false) {
            echo "❌ ERROR en: $path\n";
            echo "   $result\n";
            $found = true;
        }
    }
}

if (!$found) {
    echo "✓ No se encontraron ParseErrors en app/\n\n";
    echo "Revisando storage/framework/views/ (compiled views)...\n";
    $compiledDir = __DIR__ . '/storage/framework/views';
    if (is_dir($compiledDir)) {
        foreach (glob($compiledDir . '/*.php') as $f) {
            $result = shell_exec("php -l " . escapeshellarg($f) . " 2>&1");
            if (strpos($result, 'No syntax errors') === false) {
                echo "❌ Compiled view: " . basename($f) . "\n";
                echo "   $result\n";
                $found = true;
            }
        }
        if (!$found) echo "✓ Compiled views también OK\n";
    }
}

echo "\nDone.\n";
