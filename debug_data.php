<?php
require_once 'config.php';
header('Content-Type: text/plain');

echo "--- Districts ---\n";
$districts = $pdo->query("SELECT id, name, slug FROM districts")->fetchAll(PDO::FETCH_ASSOC);
print_r($districts);

echo "\n--- Announcements ---\n";
$announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
print_r($announcements);

echo "\n--- Events ---\n";
$events = $pdo->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
print_r($events);
?>
