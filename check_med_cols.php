<?php
$pdo = new PDO('mysql:host=localhost;dbname=rehber_db', 'root', '21212121');

$cols = $pdo->query('SHOW COLUMNS FROM hospitals')->fetchAll(PDO::FETCH_ASSOC);
echo "Hospitals columns:\n";
foreach($cols as $c) echo $c['Field'] . "\n";

$cols = $pdo->query('SHOW COLUMNS FROM pharmacies')->fetchAll(PDO::FETCH_ASSOC);
echo "\nPharmacies columns:\n";
foreach($cols as $c) echo $c['Field'] . "\n";
