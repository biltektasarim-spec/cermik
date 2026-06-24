<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT id, business_name, working_hours, hotel_info FROM businesses");
    echo json_encode($stmt->fetchAll());
} catch (Exception $e) { echo $e->getMessage(); }
?>
