<?php
require_once 'config.php';

echo "--- Testing get_announcements filtering and sorting ---\n";
// Mock session
$_SESSION['district_id'] = 1; // Assume Cermik is 1
$url = "http://localhost:8080/SON/api/get_announcements.php";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$resp = curl_exec($ch);
$data = json_decode($resp, true);
echo "Count: " . ($data['count'] ?? 0) . "\n";
if (isset($data['data'][0])) {
    echo "First Announcement Created At: " . $data['data'][0]['created_at'] . "\n";
}

echo "\n--- Testing user_auth.php quick_login_request ---\n";
$url = "http://localhost:8080/SON/api/user_auth.php?action=quick_login_request";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['phone' => '05554443221']); // A test phone
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$resp = curl_exec($ch);
echo "Response: $resp\n";

echo "\n--- Testing user_auth.php get_sms_config via direct call mock ---\n";
require_once 'api/user_auth.php';
// Note: user_auth.php calls die/exit on send_json, so we can't easily call functions if they are inside the logic
// But we can check if it loaded without syntax errors.
?>
