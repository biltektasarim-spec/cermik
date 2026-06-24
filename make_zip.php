<?php
$zip = new ZipArchive();
$filename = "update.zip";
if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("Cannot open <$filename>\n");
}

function addFolderToZip($folder, $zipFile, $exclusiveLength) {
    $handle = opendir($folder);
    while (false !== ($f = readdir($handle))) {
        if ($f != '.' && $f != '..') {
            $filePath = "$folder/$f";
            $localPath = substr($filePath, $exclusiveLength);
            
            if (is_file($filePath)) {
                if (is_readable($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } else {
                    echo "Skipping unreadable file: $filePath\n";
                }
            } elseif (is_dir($filePath)) {
                $zipFile->addEmptyDir($localPath);
                addFolderToZip($filePath, $zipFile, $exclusiveLength);
            }
        }
    }
    closedir($handle);
}

$folders = ['assets', 'uploads', 'includes'];
foreach ($folders as $folder) {
    $zip->addEmptyDir($folder);
    addFolderToZip($folder, $zip, 0); // exclusive length is 0 so it keeps root
}

$zip->close();
echo "Zip created.\n";
?>
