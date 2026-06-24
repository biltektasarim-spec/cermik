<?php
$pdo = new PDO('mysql:host=localhost;dbname=rehber_db', 'root', '21212121');
$stmt = $pdo->query('SHOW CREATE TABLE places');
print_r($stmt->fetch());
