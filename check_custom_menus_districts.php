<?php
require_once 'config.php';
$districts = [3, 5];
$results = [];
foreach ($districts as $id) {
    $stmt = $pdo->prepare("SELECT * FROM custom_menus WHERE district_id = ? OR district_id = 0");
    $stmt->execute([$id]);
    $results[$id] = $stmt->fetchAll();
}
header('Content-Type: application/json');
echo json_encode($results);
?>
