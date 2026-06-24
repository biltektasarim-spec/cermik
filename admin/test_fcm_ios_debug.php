<?php
// Güvenlik: Sadece yerel veya admin erişimi
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../config.php';

echo "=== FCM DEBUG SCRIPT ===\n";

// Doğru yol: admin/ klasöründen bir üst çıkıp uploads/ klasörüne gir
$json_path = __DIR__ . '/../uploads/firebase-adminsdk.json';
echo "Looking for JSON at: $json_path\n";
if (!file_exists($json_path)) {
    // Alternatif yolu dene
    $json_path2 = __DIR__ . '/includes/firebase-adminsdk.json';
    echo "Not found. Trying: $json_path2\n";
    if (!file_exists($json_path2)) {
        echo "JSON not found in either location!\n";
        exit;
    }
    $json_path = $json_path2;
}
echo "JSON FOUND at: $json_path\n";

$key_info = json_decode(file_get_contents($json_path), true);

// 1. Get Token
$real_now = time();
$header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
$payload = json_encode([
    'iss'   => $key_info['client_email'],
    'scope' => 'https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/firebase.messaging',
    'aud'   => 'https://oauth2.googleapis.com/token',
    'exp'   => $real_now + 3300,
    'iat'   => $real_now - 60
]);

$b64h = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
$b64p = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
$signature = '';
openssl_sign($b64h . "." . $b64p, $signature, $key_info['private_key'], OPENSSL_ALGO_SHA256);
$b64s = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
$jwt = $b64h . "." . $b64p . "." . $b64s;

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query(['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$token_response = curl_exec($ch);
curl_close($ch);
$token_data = json_decode($token_response, true);
$access_token = $token_data['access_token'] ?? '';

if (!$access_token) {
    echo "Failed to get access token: $token_response\n";
    exit;
}

echo "Access Token: " . substr($access_token, 0, 10) . "... (Valid)\n";

// 2. Fetch the problematic form from DB
// Sadece cek_gonder_forms tablosundan fcm_token'ı olan son 5 kaydı alalım
$stmt = $pdo->query("
    SELECT * 
    FROM cek_gonder_forms 
    WHERE fcm_token IS NOT NULL AND fcm_token != '' 
    ORDER BY id DESC LIMIT 5
");
$forms = $stmt->fetchAll();

foreach ($forms as $form) {
    $fcm_token = trim($form['fcm_token']);
    if (strlen($fcm_token) > 0) {
        echo "\nTesting Form ID: " . $form['id'] . " | Token Length: " . strlen($fcm_token) . "\n";
        echo "Token Start: " . substr($fcm_token, 0, 15) . "...\n";
        
        $pushTitle = "Test Title";
        $pushBody = "Test Body";
        
        $fcm_payload = [
            'message' => [
                'token' => trim($fcm_token),
                'notification' => [
                    'title' => $pushTitle,
                    'body' => $pushBody
                ]
            ]
        ];
        
        $json_payload = json_encode($fcm_payload, JSON_UNESCAPED_UNICODE);
        if ($json_payload === false) {
            echo "JSON ENCODE ERROR: " . json_last_error_msg() . "\n";
            continue;
        }

        echo "JSON Payload: " . $json_payload . "\n";

        $fcm_url = "https://fcm.googleapis.com/v1/projects/" . trim($key_info['project_id']) . "/messages:send";
        
        $ch2 = curl_init($fcm_url);
        curl_setopt_array($ch2, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer " . $access_token,
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS     => $json_payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLINFO_HEADER_OUT    => true // Request Headers görmek için
        ]);
        $fcm_response = curl_exec($ch2);
        $fcm_http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        $req_headers = curl_getinfo($ch2, CURLINFO_HEADER_OUT);
        curl_close($ch2);

        echo "HTTP Code: $fcm_http_code\n";
        echo "Response: $fcm_response\n";
        if ($fcm_http_code == 401) {
            echo "Request Headers Sent:\n$req_headers\n";
        }
    }
}
echo "\nDONE.\n";
