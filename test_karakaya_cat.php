<?php 
require_once 'config.php'; 
$stmt = $pdo->query("SELECT id, name, category FROM places WHERE id = 26"); 
print_r($stmt->fetch(PDO::FETCH_ASSOC)); 
?>
