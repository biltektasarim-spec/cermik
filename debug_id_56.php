<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT * FROM places WHERE id = 56");
$stmt->execute();
echo "<pre>";
print_r($stmt->fetch(PDO::FETCH_ASSOC));
echo "</pre>";
?>
