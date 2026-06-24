<?php
require_once 'c:/AppServ/www/REHBER/config.php';

function get_fcm_v1_access_token($json_path) {
    if (!file_exists($json_path)) return false;
    $key_info = json_decode(file_get_contents($json_path), true);
    if (!isset($key_info['client_email']) || !isset($key_info['private_key'])) return false;

    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    $now = time();
    $payload = json_encode([
        'iss' => $key_info['client_email'],
        'scope' => 'https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now
    ]);

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    $signature = '';
    openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $key_info['private_key'], OPENSSL_ALGO_SHA256);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $token_info = json_decode($response, true);
    return $token_info['access_token'] ?? false;
}

$backend_json_path = 'c:/AppServ/www/REHBER/admin/includes/firebase-adminsdk.json';
$access_token = false;
$project_id = '';

if (file_exists($backend_json_path)) {
    $key_info = @json_decode(file_get_contents($backend_json_path), true);
    $project_id = $key_info['project_id'] ?? '';
    echo "Project ID: " . $project_id . "\n";
    $access_token = get_fcm_v1_access_token($backend_json_path);
    echo "Access Token retrieved: " . ($access_token ? "Yes" : "No") . "\n";
} else {
    echo "Backend JSON not found.\n";
    exit;
}

if ($access_token && $project_id) {
    $url = 'https://fcm.googleapis.com/v1/projects/' . $project_id . '/messages:send';
    
    $settings = get_settings($pdo, 0);
    $fcm_topic = $settings['firebase_fcm_topic'] ?? 'all_users';

    $message_payload = [
        'message' => [
            'topic' => $fcm_topic,
            'notification' => [
                'title' => 'Test',
                'body' => 'Test body'
            ]
        ]
    ];
    
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message_payload));
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: " . $httpCode . "\n";
    echo "Result: " . $result . "\n";
}
