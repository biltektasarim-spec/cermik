<?php
require 'config.php';
$district_id = 3;
$stmt = $pdo->prepare("SELECT category, COUNT(*) as count FROM places WHERE district_id = ? GROUP BY category");
$stmt->execute([$district_id]);
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "### PLACES CATEGORIES (District 3) ###\n";
print_r($res);

$stmt2 = $pdo->prepare("SELECT category, COUNT(*) as count FROM businesses WHERE district_id = ? GROUP BY category");
$stmt2->execute([$district_id]);
$res2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "\n### BUSINESSES CATEGORIES (District 3) ###\n";
print_r($res2);
?>
