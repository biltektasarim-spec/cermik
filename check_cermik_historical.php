<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT id, name, category, panorama_360, lat, lng FROM places WHERE district_id = 3 AND category = 'Historical'");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
