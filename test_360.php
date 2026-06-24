<?php
require 'config.php';
$stmt = $pdo->query("DESCRIBE hospitals");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cols);
