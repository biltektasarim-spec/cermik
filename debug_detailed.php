<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT id, name, name_en, slogan, slogan_en, district_id FROM places WHERE category = 'HotSpring'");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($results);
echo "</pre>";
?>
