<?php
$files = [
    'resources/views/components/notifications.blade.php',
    'resources/views/ventas/create.blade.php',
    'resources/views/compras/create.blade.php',
    'resources/views/layouts/topbar.blade.php',
    'resources/views/clientes/index.blade.php',
    'resources/views/laboratorios/index.blade.php',
    'resources/views/proveedores/index.blade.php',
    'resources/views/productos/index.blade.php',
    'resources/views/categorias/index.blade.php',
    'resources/views/presentaciones/index.blade.php',
    'resources/views/presentaciones/show.blade.php',
    'resources/views/recetas/index.blade.php',
    'resources/views/inventario/alertas.blade.php',
    'resources/views/cajas/index.blade.php',
    'resources/views/promociones/index.blade.php',
];

foreach ($files as $f) {
    if (!file_exists($f)) { echo "MISSING: $f\n"; continue; }
    $content = file_get_contents($f);
    $opens  = substr_count($content, '[');
    $closes = substr_count($content, ']');
    $parens_o = substr_count($content, '(');
    $parens_c = substr_count($content, ')');
    $flag = '';
    if ($opens !== $closes) $flag .= " BRACKET_MISMATCH([$opens vs ]$closes)";
    if ($parens_o !== $parens_c) $flag .= " PAREN_MISMATCH(($parens_o vs )$parens_c)";
    if ($flag) {
        echo "⚠ $f$flag\n";
    } else {
        echo "✓ $f\n";
    }
}
echo "\nDone.\n";
