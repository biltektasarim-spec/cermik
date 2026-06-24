<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT * FROM custom_menus WHERE id = 60");
$stmt->execute();
$menu = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Custom Menu ID 60 Data:\n";
print_r($menu);
?>
