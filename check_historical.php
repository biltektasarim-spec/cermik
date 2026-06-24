<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT id, name, category, district_id, is_approved FROM places WHERE district_id = 3 AND category = 'Historical'");
$stmt->execute();
header('Content-Type: application/json');
echo json_encode($stmt->fetchAll());
?>
