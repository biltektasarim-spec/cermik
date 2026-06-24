<?php
require_once 'config.php';
$tables = ['districts', 'events', 'users', 'settings', 'businesses', 'places'];
foreach ($tables as $table) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "$table: $count rows<br>";
    } catch (Exception $e) {
        echo "$table: Error - " . $e->getMessage() . "<br>";
    }
}
?>
