<?php
require_once 'config.php';

echo "--- PLACES CATEGORIES ---\n";
$res = $conn->query("SELECT DISTINCT category FROM places");
while($row = $res->fetch_assoc()) echo "- " . ($row['category'] ?? 'NULL') . "\n";

echo "\n--- BUSINESSES CATEGORIES ---\n";
$res = $conn->query("SELECT DISTINCT category FROM businesses");
while($row = $res->fetch_assoc()) echo "- " . ($row['category'] ?? 'NULL') . "\n";
?>
