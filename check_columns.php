<?php
require_once 'config.php';
$stmt = $pdo->query("SHOW COLUMNS FROM custom_menus");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
file_put_contents('custom_menus_columns.txt', implode("\n", $columns));
echo "Columns saved to custom_menus_columns.txt";
?>
