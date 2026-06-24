<?php
require_once 'config.php';
$stmt = $pdo->query("DESCRIBE hospitals");
echo json_encode($stmt->fetchAll(), JSON_PRETTY_PRINT);
?>
