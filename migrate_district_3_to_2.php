<?php
require_once 'config.php';

$tables = ['announcements', 'events', 'places', 'municipal_guides', 'check_ins', 'cek_gonder_forms', 'live_broadcasts', 'services', 'weather_stats'];

echo "--- COMPREHENSIVE MIGRATION (ID 3 -> 2) ---\n";

foreach ($tables as $table) {
    try {
        $stmt = $pdo->prepare("UPDATE $table SET district_id = 2 WHERE district_id = 3");
        $stmt->execute();
        $count = $stmt->rowCount();
        echo "Table $table: Moved $count records.\n";
    } catch (Exception $e) {
        echo "Table $table ignore: " . $e->getMessage() . "\n";
    }
}

echo "--- MIGRATION COMPLETED ---\n";
?>
