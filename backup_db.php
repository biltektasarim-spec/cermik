<?php
require_once 'config.php';

$backup_file = 'rehber_db_backup_' . date('Ymd_His') . '.sql';
$handle = fopen($backup_file, 'w');

if (!$handle) {
    die("Could not create backup file.");
}

$tables = [];
$result = $pdo->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    $result = $pdo->query("SELECT * FROM $table");
    $num_fields = $result->columnCount();

    fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
    $row2 = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
    fwrite($handle, $row2[1] . ";\n\n");

    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        fwrite($handle, "INSERT INTO `$table` VALUES(");
        for ($j = 0; $j < $num_fields; $j++) {
            $row[$j] = addslashes($row[$j]);
            $row[$j] = str_replace("\n", "\\n", $row[$j]);
            if (isset($row[$j])) {
                fwrite($handle, '"' . $row[$j] . '"');
            } else {
                fwrite($handle, 'NULL');
            }
            if ($j < ($num_fields - 1)) {
                fwrite($handle, ',');
            }
        }
        fwrite($handle, ");\n");
    }
    fwrite($handle, "\n\n\n");
}

fclose($handle);
echo "Backup created successfully: $backup_file";
?>
