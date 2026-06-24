<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT id, district_id, business_name, category, is_approved, is_active FROM businesses WHERE district_id IN (3,5)");
    echo json_encode($stmt->fetchAll());
} catch (Exception $e) { echo $e->getMessage(); }
?>
