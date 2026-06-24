<?php
require_once 'config.php';
try {
    // Rename 'Karakaya Barajı' to 'Karakaya' and update image
    $pdo->prepare("UPDATE places SET name = 'Karakaya', image_main = 'uploads/karakayadam1.jpg' WHERE id = 26")->execute();
    echo "Karakaya updated successfully (Name & Image).\n";

    // Re-verify the count for HotSpring in Cermik for the API
    $stmt = $pdo->prepare("SELECT id, name, category FROM places WHERE district_id = 3 AND category = 'HotSpring'");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
