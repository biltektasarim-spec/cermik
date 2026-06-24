<?php
require_once 'config.php';
// ID 26 (Admin Karakaaya Edit) and ID 48 (Karakaya Nature) comparison
$stmt = $pdo->prepare("SELECT id, name, category, image_main, updated_at FROM places WHERE id IN (26, 48)");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
