<?php
require_once 'config.php';
$stmt = $pdo->query("DESCRIBE custom_menus");
echo "<pre>";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
?>
