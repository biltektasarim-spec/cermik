<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT sms_title, sms_api_user, sms_api_key, google_client_id FROM settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "SMS Settings:\n";
    print_r($settings);
    
    echo "\nUser Table Sample:\n";
    $stmt = $pdo->query("SELECT id, email, phone, is_active, google_id FROM users LIMIT 3");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
