<?php
require_once 'config.php';
echo "--- DISTRICT 3 (CERMIK) ---\n";
$stmt = $pdo->prepare("SELECT name, value FROM settings WHERE district_id = 3");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- DISTRICT 5 (CUNGUS) ---\n";
$stmt = $pdo->prepare("SELECT name, value FROM settings WHERE district_id = 5");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
