<?php
require 'config.php';
$district_id = 3;
$stmt = $pdo->prepare("SELECT id, name_tr, sort_order FROM custom_menus WHERE district_id = ? AND is_active = 1 ORDER BY sort_order ASC");
$stmt->execute([$district_id]);
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "### CUSTOM MENUS ORDER (District 3) ###\n";
print_r($res);
?>
