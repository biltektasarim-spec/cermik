<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT * FROM settings WHERE name LIKE 'site_name%'");
echo "<pre>";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
?>
