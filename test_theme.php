<?php
define('CLI_SCRIPT', true);
require('/var/www/html/public/config.php');

$class = 'theme_adaptable\output\core_renderer';
echo "Testing class: $class\n";

if (class_exists($class)) {
    echo "✅ Class exists\n";
    if (method_exists($class, 'firstview_fakeblocks')) {
        echo "✅ Method firstview_fakeblocks exists\n";
    } else {
        echo "❌ Method firstview_fakeblocks NOT found\n";
    }
} else {
    echo "❌ Class NOT found\n";
}

echo "\nAll registered themes:\n";
$themelist = get_config('', 'themelist');
echo $themelist . "\n";
