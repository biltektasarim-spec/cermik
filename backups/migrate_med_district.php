<?php
$pdo = new PDO('mysql:host=localhost;dbname=rehber_db', 'root', '21212121');
try {
    $pdo->exec("ALTER TABLE hospitals ADD COLUMN district_id INT NOT NULL DEFAULT 3 AFTER id");
    echo "Added district_id to hospitals.\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE pharmacies ADD COLUMN district_id INT NOT NULL DEFAULT 3 AFTER id");
    echo "Added district_id to pharmacies.\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }
