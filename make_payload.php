<?php
// Generates a self-extracting PHP script to deploy files
$folders = ['assets', 'uploads', 'includes'];
$files = [];

function getFilesFromDir($dir, &$results = array()){
    $files = scandir($dir);
    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
            $results[] = $path;
        } else if($value != "." && $value != "..") {
            getFilesFromDir($path, $results);
            $results[] = $path;
        }
    }
    return $results;
}

$allFilePaths = [];
foreach ($folders as $f) {
    if (is_dir($f)) {
        getFilesFromDir($f, $allFilePaths);
    }
}

$payload = "<?php\n";
$payload .= "\$files = array();\n";

$baseDir = str_replace('\\', '/', __DIR__ . '/');

foreach ($allFilePaths as $path) {
    $path = str_replace('\\', '/', $path);
    $relPath = str_replace($baseDir, '', $path);
    
    // Ignore unreadable files (permission issues)
    if (!is_readable($path)) continue;
    
    if (is_dir($path)) {
       $payload .= "\$files['{$relPath}'] = 'DIR';\n";
    } else {
       // Ignore big files over 10MB to avoid memory limit? No, let's just use base64
       $content = base64_encode(file_get_contents($path));
       $payload .= "\$files['{$relPath}'] = '{$content}';\n";
    }
}

$payload .= <<<EOF
\$ds = DIRECTORY_SEPARATOR;
foreach (\$files as \$path => \$content) {
    if (\$content === 'DIR') {
        if (!is_dir(__DIR__ . \$ds . \$path)) {
            mkdir(__DIR__ . \$ds . \$path, 0755, true);
        }
    } else {
        \$dir = dirname(__DIR__ . \$ds . \$path);
        if (!is_dir(\$dir)) {
            mkdir(\$dir, 0755, true);
        }
        file_put_contents(__DIR__ . \$ds . \$path, base64_decode(\$content));
    }
}

// Also fix DB encoding since we couldn't do it before
require_once 'config.php';
try {
    \$pdo->exec("SET NAMES utf8mb4");
    \$fixes = [
        'mayor_name' => 'Şehmus KARAMEHMETOĞLU',
        'mayor_title' => 'Çermik Belediye Başkanı',
        'site_name' => 'Çermik Belediyesi',
        'site_email' => 'admin@cermik.bel.tr',
        'copyright_text' => 'Rotamız Çermik',
        'explore_desc' => 'Tarihin Sıcaklığı Doğanın Saklı Yüzü',
        'explore_desc_en' => 'The Warmth of History, The Hidden Face of Nature'
    ];
    \$stmt = \$pdo->prepare("UPDATE settings SET value = ? WHERE name = ?");
    foreach (\$fixes as \$name => \$value) {
        \$stmt->execute([\$value, \$name]);
    }
    
    \$pdo->exec("UPDATE settings SET value = REPLACE(value, '├ç', 'Ç')");
    \$pdo->exec("UPDATE settings SET value = REPLACE(value, '├ğ', 'ç')");
    \$pdo->exec("UPDATE settings SET value = REPLACE(value, '┼ş', 'ş')");
    \$pdo->exec("UPDATE settings SET value = REPLACE(value, '┼Ş', 'Ş')");
    \$pdo->exec("UPDATE settings SET value = REPLACE(value, '─▒', 'ı')");
    \$pdo->exec("UPDATE settings SET value = REPLACE(value, '─░', 'İ')");
    \$pdo->exec("UPDATE settings SET value = REPLACE(value, '├Â', 'ö')");
    \$pdo->exec("UPDATE settings SET value = REPLACE(value, '├Ö', 'Ö')");
    \$pdo->exec("UPDATE settings SET value = REPLACE(value, '├╝', 'ü')");
    \$pdo->exec("UPDATE settings SET value = REPLACE(value, '├£', 'Ü')");
    \$pdo->exec("UPDATE settings SET value = REPLACE(value, '─ş', 'ğ')");
    \$pdo->exec("UPDATE settings SET value = REPLACE(value, '─Ş', 'Ğ')");

    echo "Files extracted and DB updated successfully!<br>";
} catch (Exception \$e) {
    echo "Files extracted but DB Error: " . \$e->getMessage() . "<br>";
}
?>
EOF;

file_put_contents('deploy_payload.php', $payload);
echo "payload created. size: " . filesize('deploy_payload.php');
?>
