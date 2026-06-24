<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=rehber_db;charset=utf8', 'root', '21212121');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add SMS columns to settings if they don't exist
    $cols = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('sms_title', $cols)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN sms_title VARCHAR(255) DEFAULT 'ROTAMIZ'");
    }
    if (!in_array('sms_api_user', $cols)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN sms_api_user VARCHAR(255) DEFAULT NULL");
    }
    if (!in_array('sms_api_key', $cols)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN sms_api_key VARCHAR(255) DEFAULT NULL");
    }

    // Update settings with provided values if they are empty
    $pdo->exec("UPDATE settings SET 
        sms_api_user = '7073c30918869aee144ddca9', 
        sms_api_key = 'bb37df2be980e603326bce12', 
        sms_title = 'ROTAMIZ' 
        WHERE id = 1 AND (sms_api_user IS NULL OR sms_api_user = '')");

    echo "Settings updated successfully.\n";

    // Check users table for is_active
    $u_cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    echo "User columns: " . implode(", ", $u_cols) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
