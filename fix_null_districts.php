<?php
$pdo = new PDO('mysql:host=localhost;dbname=rehber_db', 'root', '21212121');
$pdo->exec('UPDATE places SET district_id = 3 WHERE district_id IS NULL');
$pdo->exec('UPDATE municipal_guide SET district_id = 3 WHERE district_id IS NULL');
echo "Updated NULL district_ids to 3.\n";
