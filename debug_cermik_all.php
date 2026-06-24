<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT id, name, category, district_id FROM places WHERE district_id = 3");
$stmt->execute();
echo "<pre>";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
?>
