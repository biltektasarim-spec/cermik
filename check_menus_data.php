<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT * FROM custom_menus ORDER BY district_id, sort_order ASC");
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($menus);
echo "</pre>";
?>
