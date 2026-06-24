<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT id, business_name FROM businesses WHERE business_name LIKE '%Granpark%'");
$stmt->execute();
$results = $stmt->fetchAll();
print_r($results);
?>
