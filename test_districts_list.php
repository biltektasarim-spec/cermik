<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT id, name FROM districts");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
