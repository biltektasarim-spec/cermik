<?php
require_once 'config.php';

try {
    $pdo->exec("ALTER TABLE custom_menus ADD COLUMN slug VARCHAR(255) NULL AFTER name_en");
    $pdo->exec("ALTER TABLE custom_menus ADD COLUMN image VARCHAR(255) NULL AFTER slug");
    $pdo->exec("ALTER TABLE custom_menus ADD COLUMN menu_type VARCHAR(50) DEFAULT 'single' AFTER image");
    echo "Columns 'slug', 'image', and 'menu_type' added successfully to 'custom_menus' table.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
