<?php
$pdo = new PDO('mysql:host=localhost;dbname=rehber_db', 'root', '21212121');
$stmt = $pdo->query("SELECT * FROM settings WHERE name LIKE 'menu_%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
