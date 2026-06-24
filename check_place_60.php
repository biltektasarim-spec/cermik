<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT * FROM places WHERE id = 60");
$stmt->execute();
$place = $stmt->fetch(PDO::FETCH_ASSOC);
echo "ID 60 Data:\n";
print_r($place);
?>
