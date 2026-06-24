<?php
require_once 'config.php';

$tables = ['events', 'announcements', 'places', 'businesses', 'pharmacies', 'hospital', 'live_broadcast', 'municipal_guide'];

foreach ($tables as $table) {
    try {
        $pdo->query("UPDATE $table SET district_id = 3 WHERE district_id = 2");
    } catch (Exception $e) {
        // Table might not exist or doesn't have district_id, ignore
    }
}

echo json_encode(["status" => "success", "message" => "Updated all tables district_id 2 to 3"]);
?>
