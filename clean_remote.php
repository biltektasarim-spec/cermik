<?php
$files_to_delete = [
    'fix_remote.php',
    'fix_remote2.php',
    'fix_encoding.php',
    'deploy_payload.php',
    'make_payload.php',
    'fix_paths.php',
    'update.zip',
    'unzip_and_fix.php',
    'test.php',
    'clean_remote.php' // deletes itself last
];

$results = [];
foreach ($files_to_delete as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            $results[] = "Deleted: $file";
        } else {
            $results[] = "Failed to delete: $file";
        }
    } else {
        $results[] = "Not found: $file";
    }
}

echo implode("\n", $results);
?>
