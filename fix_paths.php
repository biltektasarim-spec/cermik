<?php
function replaceInFile($path, $search, $replace) {
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (strpos($content, $search) !== false) {
            $content = str_replace($search, $replace, $content);
            file_put_contents($path, $content);
            echo "Fixed: $path\n";
        }
    }
}

// 1. Fix bottom_nav.php
replaceInFile(
    'c:/AppServ/www/REHBER/includes/bottom_nav.php', 
    "location.href='/REHBER/index.php'", 
    "location.href='<?php echo (strpos(\$_SERVER[\'HTTP_HOST\'], \'localhost\') !== false) ? \'/REHBER/\' : \'/\'; ?>'"
);

// 2. Fix url('assets/...) in cermik and cungus
$filesToFix = [
    'c:/AppServ/www/REHBER/cermik/index.php',
    'c:/AppServ/www/REHBER/cermik/pharmacy.php',
    'c:/AppServ/www/REHBER/cungus/index.php',
    'c:/AppServ/www/REHBER/cungus/kaplica.php',
    'c:/AppServ/www/REHBER/cungus/pharmacy.php'
];

foreach ($filesToFix as $file) {
    replaceInFile($file, "url('assets/img", "url('../assets/img");
}

echo "Local files updated.\n";
?>
