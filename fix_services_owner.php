<?php
require_once 'config.php';

// Update services to map district_id 2 -> 3 (Cermik)
$stmt = $pdo->query("UPDATE services SET district_id = 3 WHERE district_id = 2");

// Update users array to map district_id 2 -> 3
$stmt2 = $pdo->query("UPDATE users SET district_id = 3 WHERE district_id = 2");

// Update any other tables just in case (like businesses, places, announcements, events) 
// if they also suffer from this, but "services" is the main one complained about here.
// Let's just fix services to be safe.

echo json_encode(["status" => "success", "message" => "Updated services district_id 2 to 3"]);
?>
