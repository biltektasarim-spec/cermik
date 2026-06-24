<?php
require_once 'config.php';
$stmt = $pdo->query("DESCRIBE places");
echo "<pre>";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
?>
