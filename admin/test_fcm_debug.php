<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';
if ($_SESSION['admin_role'] !== 'SUPER_ADMIN') die('Yetkisiz');

echo "<h2>FCM Detaylı Tanılama</h2><pre>";

// 1. JSON dosyasını bul ve oku
$json_path = __DIR__ . '/../uploads/firebase-adminsdk.json';
if (!file_exists($json_path)) $json_path = __DIR__ . '/includes/firebase-adminsdk.json';

echo "✅ JSON Yolu: $json_path\n\n";

$key_info = json_decode(file_get_contents($json_path), true);
echo "📋 Proje ID     : " . ($key_info['project_id'] ?? 'YOK') . "\n";
echo "📋 Client Email : " . ($key_info['client_email'] ?? 'YOK') . "\n";
echo "📋 Private Key  : " . (isset($key_info['private_key']) ? substr($key_info['private_key'], 0, 60) . '...' : 'YOK') . "\n\n";

// Google'dan gerçek zamanı al (GET request ile Date header)
$real_now = time();
$tc = curl_init('https://oauth2.googleapis.com/.well-known/openid-configuration');
curl_setopt_array($tc, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true,
    CURLOPT_HTTPGET => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_TIMEOUT => 8]);
$tc_resp = curl_exec($tc);
curl_close($tc);
if ($tc_resp && preg_match('/Date:\s*(.+)/i', $tc_resp, $dm)) {
    $gt = strtotime(trim($dm[1]));
    if ($gt > 0) { $real_now = $gt; echo "⏰ Google Zamanı  : " . date('Y-m-d H:i:s', $real_now) . " (UTC)\n"; }
} else {
    echo "⚠️  Google'dan zaman alınamadı, sunucu saati kullanılıyor: " . date('Y-m-d H:i:s', $real_now) . "\n";
}

$header  = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
$payload = json_encode([
    'iss'   => $key_info['client_email'],
    'scope' => 'https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/firebase.messaging',
    'aud'   => 'https://oauth2.googleapis.com/token',
    'exp'   => $real_now + 3300,
    'iat'   => $real_now - 60
]);

$b64h = str_replace(['+','/',  '='], ['-','_',''], base64_encode($header));
$b64p = str_replace(['+','/',  '='], ['-','_',''], base64_encode($payload));
$sig  = '';
$ok   = openssl_sign("$b64h.$b64p", $sig, $key_info['private_key'], OPENSSL_ALGO_SHA256);
echo "🔐 JWT İmza    : " . ($ok ? "✅ Başarılı" : "❌ HATA: " . openssl_error_string()) . "\n\n";

$jwt = "$b64h.$b64p." . str_replace(['+','/',  '='], ['-','_',''], base64_encode($sig));

// 3. OAuth2 token al
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query(['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 15,
]);
$token_response  = curl_exec($ch);
$token_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$token_curl_err  = curl_error($ch);
curl_close($ch);

echo "🌐 OAuth2 HTTP Kodu : $token_http_code\n";
if ($token_curl_err) echo "❌ cURL Hatası     : $token_curl_err\n";
$token_data   = json_decode($token_response, true);
$access_token = $token_data['access_token'] ?? '';

if ($access_token) {
    echo "✅ Access Token    : " . substr($access_token, 0, 40) . "...\n\n";
} else {
    echo "❌ Token ALINAMADI :\n$token_response\n\n";
    echo "</pre>";
    exit;
}

// 4. FCM'e test mesajı gönder (topic)
$project_id = $key_info['project_id'];
$fcm_url    = "https://fcm.googleapis.com/v1/projects/$project_id/messages:send";

$payload_fcm = json_encode([
    'message' => [
        'topic'        => 'all_users',
        'notification' => ['title' => 'Test', 'body' => 'FCM Tanılama Testi'],
    ]
]);

$ch2 = curl_init($fcm_url);
curl_setopt_array($ch2, [
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ["Authorization: Bearer $access_token", "Content-Type: application/json"],
    CURLOPT_POSTFIELDS     => $payload_fcm,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 15,
]);
$fcm_response  = curl_exec($ch2);
$fcm_http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "📡 FCM URL       : $fcm_url\n";
echo "📡 FCM HTTP Kodu : $fcm_http_code\n";
echo "📡 FCM Yanıtı    :\n$fcm_response\n";

echo "</pre>";
