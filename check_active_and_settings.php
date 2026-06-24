<?php
require_once 'config.php';

function checkTable($name, $district_id) {
    global $pdo;
    echo "--- TABLE: $name (District: $district_id) ---\n";
    $stmt = $pdo->prepare("SELECT id, name, category, is_active, is_approved FROM $name WHERE district_id = ?");
    $stmt->execute([$district_id]);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "\n";
}

checkTable('places', 3);
checkTable('places', 5);
checkTable('businesses', 3);
checkTable('businesses', 5);

echo "--- SETTINGS (District 0 vs 3 vs 5) ---\n";
$stmt = $pdo->query("SELECT district_id, name, value FROM settings WHERE district_id IN (0, 3, 5) AND (name LIKE 'site_%' OR name LIKE 'menu_%')");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>
