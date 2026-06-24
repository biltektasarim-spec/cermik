<?php
require_once 'config.php';
$tables = ['places', 'businesses', 'categories'];
foreach ($tables as $table) {
    echo "--- TABLE: $table ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['Field'] . " (" . $row['Type'] . ") - Key: " . $row['Key'] . "\n";
        }
        
        echo "\n--- SAMPLE DATA: $table (First 3) ---\n";
        $stmt = $pdo->query("SELECT * FROM $table LIMIT 3");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
?>
