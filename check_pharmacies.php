<?php
$pdo = new PDO('mysql:host=localhost;dbname=rehber_db', 'root', '21212121');
$stmt = $pdo->query('SHOW COLUMNS FROM pharmacies');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
