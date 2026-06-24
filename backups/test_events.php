<?php
require_once 'c:/AppServ/www/REHBER/config.php';
$stmt = $pdo->query("SELECT * FROM events");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($events);
?>
