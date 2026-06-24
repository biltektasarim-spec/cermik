<?php
require_once 'config.php';
try {
    $user_cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    $queries = [];
    if (!in_array('otp_code', $user_cols)) {
        $queries[] = "ALTER TABLE users ADD COLUMN `otp_code` VARCHAR(10) NULL DEFAULT NULL";
    }
    if (!in_array('otp_expiry', $user_cols)) {
        $queries[] = "ALTER TABLE users ADD COLUMN `otp_expiry` DATETIME NULL DEFAULT NULL";
    }
    if (!in_array('is_verified', $user_cols)) {
        $queries[] = "ALTER TABLE users ADD COLUMN `is_verified` TINYINT(1) NOT NULL DEFAULT 0";
    }
    if (!in_array('google_id', $user_cols)) {
        $queries[] = "ALTER TABLE users ADD COLUMN `google_id` VARCHAR(100) NULL DEFAULT NULL";
    }
    if (!in_array('profile_image', $user_cols)) {
        $queries[] = "ALTER TABLE users ADD COLUMN `profile_image` VARCHAR(500) NULL DEFAULT NULL";
    }
    if (!in_array('last_login_at', $user_cols)) {
        $queries[] = "ALTER TABLE users ADD COLUMN `last_login_at` DATETIME NULL DEFAULT NULL";
    }
    
    foreach ($queries as $q) {
        $pdo->exec($q);
        echo "Executed: $q\n";
    }
    echo "Migration completed successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
