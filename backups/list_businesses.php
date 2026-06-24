<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT id, business_name FROM businesses");
$stmt->execute();
$results = $stmt->fetchAll();
print_r($results);
?>
