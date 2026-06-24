<?php
$pdo = new PDO('mysql:host=localhost;dbname=rehber_db', 'root', '21212121');
$stmt = $pdo->query("SELECT id, name, district_id FROM places WHERE district_id IS NULL");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt = $pdo->query("SELECT id, business_name, district_id FROM businesses WHERE district_id IS NULL");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt = $pdo->query("SELECT id, title, district_id FROM municipal_guide WHERE district_id IS NULL");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
