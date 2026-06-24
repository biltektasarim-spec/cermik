<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT id, business_name, category, district_id, is_approved, is_active FROM businesses WHERE district_id IN (3,4)");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results, JSON_PRETTY_PRINT);
?>
