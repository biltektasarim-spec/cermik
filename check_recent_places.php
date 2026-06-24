<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT id, name, category, image_main, panorama_360 FROM places ORDER BY id DESC LIMIT 20");
$stmt->execute();
$places = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Recent Places:\n";
print_r($places);
?>
