<?php
$pdo = new PDO('mysql:host=localhost;dbname=rehber_db', 'root', '21212121');
$stmt = $pdo->query("SHOW COLUMNS FROM places LIKE 'category'");
$col = $stmt->fetch();
echo $col['Type'] . "\n";
