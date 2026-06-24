<?php
require_once 'config.php';
$stmt = $pdo->query("DESCRIBE services");
echo "<pre>";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
?>
