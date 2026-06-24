<?php
$pdo = new PDO('mysql:host=localhost;dbname=rehber_db', 'root', '21212121');
$stmt = $pdo->query("SELECT * FROM settings WHERE district_id != 0");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
