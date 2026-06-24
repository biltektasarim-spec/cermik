<?php
require_once 'config.php';
$pdo->exec("UPDATE custom_menus SET icon = 'fa-link' WHERE icon = 'fa-star'");
echo "Updated fa-star to fa-link in custom_menus table.";
?>
