<?php
require_once 'config.php';
$tables = ['settings', 'users'];
foreach ($tables as $table) {
    echo "--- TABLE: $table ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['Field'] . " (" . $row['Type'] . ") - Key: " . $row['Key'] . "\n";
        }
        echo "--- INDEXES: $table ---\n";
        $stmt = $pdo->query("SHOW INDEX FROM $table");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Name: " . $row['Key_name'] . " - Column: " . $row['Column_name'] . " - Unique: " . ($row['Non_unique'] == 0 ? 'Yes' : 'No') . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
?>
