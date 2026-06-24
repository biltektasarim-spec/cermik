<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $ai_context = $_POST['ai_context'];
    
    // Road stop data
    $poi_name = $_POST['poi_name'] ?? '';
    $trigger_radius = $_POST['trigger_radius'] ?? 100;
    $audio_script = $_POST['audio_script'] ?? '';

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO places (name, category, description, lat, lng, ai_context) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $description, $lat, $lng, $ai_context]);
        $place_id = $pdo->lastInsertId();

        if (!empty($poi_name)) {
            $stmt = $pdo->prepare("INSERT INTO road_stops (parent_place_id, poi_name, trigger_radius, audio_script) VALUES (?, ?, ?, ?)");
            $stmt->execute([$place_id, $poi_name, $trigger_radius, $audio_script]);
        }

        $pdo->commit();
        header("Location: ../places_archive.php?category=" . urlencode($category) . "&msg=success");
    } catch (\PDOException $e) {
        $pdo->rollBack();
        die("Hata: " . $e->getMessage());
    }
}
?>
