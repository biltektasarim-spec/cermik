<?php
require_once 'config.php';
try {
    $p = $pdo->query("SELECT id, name, district_id, is_on_duty FROM pharmacies")->fetchAll();
    $h = $pdo->query("SELECT id, name, district_id FROM hospitals")->fetchAll();
    echo json_encode([
        'pharmacies' => $p,
        'hospitals' => $h
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) { echo $e->getMessage(); }
?>
