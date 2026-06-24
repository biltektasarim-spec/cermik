<?php
// make_deployment_zip.php
$zip = new ZipArchive();
$filename = "deploy_fixed.zip";

if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("Cannot open <$filename>\n");
}

$root = __DIR__;
$exclude_dirs = ['.agents', '.idea', 'node_modules', '_eski_sql_dosyalari', 'sessions', 'CermikRehberiApp'];
$exclude_files = [
    'deploy_fixed.zip', 
    'deploy_payload.php', 
    'database_backup.sql', 
    'db_import_helper.php', 
    'REHBER.zip',
    'auth_debug.log',
    'sms_debug.log'
];

function addRecursive($dir, $zip, $root, $exclude_dirs, $exclude_files) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($files as $name => $file) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($root) + 1);
        $relativePath = str_replace('\\', '/', $relativePath);

        // Check if in excluded dir
        foreach ($exclude_dirs as $ex_dir) {
            if (strpos($relativePath, $ex_dir . '/') === 0 || $relativePath === $ex_dir) {
                continue 2;
            }
        }

        // Check if excluded file
        if (in_array(basename($relativePath), $exclude_files)) {
            continue;
        }

        if ($file->isDir()) {
            $zip->addEmptyDir($relativePath);
        } else {
            $zip->addFile($filePath, $relativePath);
        }
    }
}

addRecursive($root, $zip, $root, $exclude_dirs, $exclude_files);

$zip->close();
echo "Deployment ZIP created successfully: $filename\n";
?>
