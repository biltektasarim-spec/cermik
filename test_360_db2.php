<?php
require 'config.php';
$stmt = $db->prepare('SELECT name, panorama360 FROM places WHERE id = 34');
$stmt->execute();
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
