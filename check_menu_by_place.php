<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT * FROM custom_menus WHERE place_id = 60");
$stmt->execute();
$menu = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Custom Menu with Place ID 60:\n";
print_r($menu);
?>
