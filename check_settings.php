<?php
require_once 'config.php';
$stmt = $pdo->query("DESCRIBE settings");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
