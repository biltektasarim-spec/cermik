<?php
require_once 'config.php';
$tables = ['check_ins', 'places', 'businesses', 'users'];
foreach ($tables as $table) {
    echo "--- TABLE: $table ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['Field'] . " (" . $row['Type'] . ") - Key: " . $row['Key'] . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
?>
