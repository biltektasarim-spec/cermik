<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT id, district_id, name, name_en, category FROM places WHERE category = 'HotSpring'");
echo "<pre>";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
?>
