<?php
require_once 'config.php';

try {
    // Create districts table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `districts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `lat` DECIMAL(10,8) DEFAULT NULL,
            `lng` DECIMAL(11,8) DEFAULT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "districts table created.<br>";

    // Insert the districts
    $stmt = $pdo->prepare("INSERT IGNORE INTO `districts` (id, name, slug, lat, lng, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->execute([1, 'Çermik', 'cermik', 38.1384, 39.4475]);
    $stmt->execute([2, 'Çüngüş', 'cungus', 38.2118, 39.2764]);
    echo "Districts inserted.<br>";

    // Update places to have district_id if not set
    $pdo->exec("ALTER TABLE places MODIFY COLUMN district_id INT DEFAULT 1") ;
    echo "Places updated.<br>";

    // Make sure settings has district_id column
    try {
        $pdo->exec("ALTER TABLE settings ADD COLUMN district_id INT DEFAULT 0");
        echo "Settings district_id column added.<br>";
    } catch (Exception $e) {
        echo "Settings district_id: " . $e->getMessage() . "<br>";
    }

    // Make sure users has district_id, role columns
    $userCols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('role', $userCols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'USER'");
        echo "Users role column added.<br>";
    } else {
        echo "Users role column exists.<br>";
    }
    if (!in_array('district_id', $userCols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN district_id INT DEFAULT NULL");
        echo "Users district_id column added.<br>";
    } else {
        echo "Users district_id column exists.<br>";
    }

    // Fix the admin user (ensure admin@admin.com has SUPER_ADMIN role)
    $pdo->exec("UPDATE users SET role='SUPER_ADMIN', is_active=1 WHERE email='admin@admin.com'");
    echo "Admin user updated.<br>";

    echo "<br><strong>All done! Now visit <a href='index.php'>index.php</a></strong>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
