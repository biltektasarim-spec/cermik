<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT id, name, name_en FROM places WHERE district_id = 3 AND category = 'HotSpring'");
$stmt->execute();
echo "<pre>";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
?>
