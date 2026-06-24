<?php
require_once 'config.php';
try {
    $stmt = $pdo->prepare("SELECT id, name, image_main, panorama_360 FROM places WHERE id = 26");
    $stmt->execute();
    print_r($stmt->fetch(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
