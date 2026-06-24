<?php
require_once 'config.php';

echo "--- DISTRICTS ---\n";
$stmt = $pdo->query("SELECT id, name, slug FROM districts");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "\n--- PLACES CATEGORIES & DISTRICTS ---\n";
$stmt = $pdo->query("SELECT district_id, category, COUNT(*) as count FROM places GROUP BY district_id, category");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "\n--- BUSINESSES CATEGORIES & DISTRICTS ---\n";
$stmt = $pdo->query("SELECT district_id, category, COUNT(*) as count FROM businesses GROUP BY district_id, category");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "\n--- SETTINGS FOR DISTRICTS (Top 20) ---\n";
$stmt = $pdo->query("SELECT id, district_id, name, value FROM settings WHERE name LIKE 'menu_%' OR name LIKE 'hero_%' ORDER BY district_id ASC LIMIT 20");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>
