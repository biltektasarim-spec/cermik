<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT DISTINCT district_id FROM businesses");
$districts = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Districts in businesses: " . implode(", ", $districts) . "\n";

$stmt2 = $pdo->query("SELECT id, name, slug FROM districts");
$all_districts = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "All districts: " . json_encode($all_districts) . "\n";
?>
