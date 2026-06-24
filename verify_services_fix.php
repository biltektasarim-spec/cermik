<?php
require_once 'config.php';
header('Content-Type: text/plain');

echo "--- CHECKING SERVICES TABLE SCHEMA ---\n";
try {
    $stmt = $pdo->query("DESCRIBE services");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n--- CHECKING SERVICES DATA SAMPLE ---\n";
try {
    $stmt = $pdo->query("SELECT * FROM services LIMIT 1");
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        print_r($data);
    } else {
        echo "No services found in table.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
