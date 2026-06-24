<?php
require 'config.php';
$stmt = $pdo->query("DESCRIBE districts");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
