<?php
require_once 'config.php';
echo "<pre>";
try {
    $user_cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('otp_code', $user_cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN `otp_code` VARCHAR(20) NULL DEFAULT NULL");
        echo "Added otp_code to users table.\n";
    }
    if (!in_array('otp_expiry', $user_cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN `otp_expiry` DATETIME NULL DEFAULT NULL");
        echo "Added otp_expiry to users table.\n";
    }
    
    // Ensure settings table has the necessary keys
    $settings_keys = ['sms_api_id', 'sms_api_key', 'sms_title', 'otp_enabled'];
    foreach ($settings_keys as $key) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE name = ?");
        $stmt->execute([$key]);
        if ($stmt->fetchColumn() == 0) {
            $default_val = '';
            if ($key == 'sms_api_id') $default_val = '7073c30918869aee144ddca9';
            if ($key == 'sms_api_key') $default_val = 'bb37df2be980e603326bce12';
            if ($key == 'sms_title') $default_val = 'ROTAREHBER';
            if ($key == 'otp_enabled') $default_val = '0';
            
            $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES (?, ?, 0)")->execute([$key, $default_val]);
            echo "Added $key to settings table.\n";
        }
    }
    
    echo "Migration completed successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
echo "</pre>";
?>
