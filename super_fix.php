<?php
require_once 'config.php';
try {
    echo "--- DISTRICT 3 (CERMIK) SETTINGS ---\n";
    $stmt3 = $pdo->prepare("SELECT name, value FROM settings WHERE district_id = 3 AND name LIKE 'menu_%_img'");
    $stmt3->execute();
    print_r($stmt3->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- DISTRICT 5 (CUNGUS) SETTINGS ---\n";
    $stmt5 = $pdo->prepare("SELECT name, value FROM settings WHERE district_id = 5 AND name LIKE 'menu_%_img'");
    $stmt5->execute();
    print_r($stmt5->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- UPDATING KARAKAYA DAM (ID 26) ---\n";
    $pdo->prepare("UPDATE places SET image_main = 'assets/img/categories/kaplica.jpg' WHERE id = 26")->execute();
    echo "ID 26 updated.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
