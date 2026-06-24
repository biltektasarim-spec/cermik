<?php
require_once 'config.php';
header('Content-Type: text/plain');

echo "--- CHECKING EVENTS TABLE SCHEMA ---\n";
try {
    $stmt = $pdo->query("DESCRIBE events");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n--- CHECKING EVENTS DATA (LATEST 5) ---\n";
try {
    $stmt = $pdo->query("SELECT id, title, district_id, status, is_global, global_status, event_date FROM events ORDER BY id DESC LIMIT 5");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($data);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
