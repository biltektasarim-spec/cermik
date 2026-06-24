<?php
require_once 'config.php';

$id_token = $_GET['id_token'] ?? '';

if (empty($id_token)) {
    die("Hata: id_token bulunamadı.");
}

// 1. Google ID Token Doğrulama
$verify_url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
$ch = curl_init($verify_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    die("Google doğrulaması başarısız oldu. Hata Kodu: " . $http_code);
}

$google_user = json_decode($response, true);
$google_id = $google_user['sub'] ?? null;
$email = $google_user['email'] ?? null;
$fname = $google_user['given_name'] ?? '';
$lname = $google_user['family_name'] ?? '';
$picture = $google_user['picture'] ?? '';

if (!$email) {
    die("Google verisi alınamadı (Email eksik).");
}

// 2. Kullanıcı Kontrolü
$stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
$stmt->execute([$google_id, $email]);
$user = $stmt->fetch();

if (!$user || empty($user['phone'])) {
    // Kayıt tamamlanmamış veya yeni kullanıcı
    // index.php'ye yönlendirip JS ile modalı açtıracağız
    $params = http_build_query([
        'needs_phone' => 1,
        'email' => $email,
        'first_name' => $fname,
        'last_name' => $lname,
        'credential' => $id_token,
        'picture' => $picture
    ]);
    header("Location: index.php?" . $params);
    exit;
}

// 3. Giriş İşlemi
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

// Google ID veya Resim eksikse güncelle
$update_fields = [];
$update_params = [];
if (empty($user['google_id'])) {
    $update_fields[] = "google_id = ?";
    $update_params[] = $google_id;
}
if (empty($user['profile_image']) && !empty($picture)) {
    $update_fields[] = "profile_image = ?";
    $update_params[] = $picture;
}
if (!empty($update_fields)) {
    $update_params[] = $user['id'];
    $pdo->prepare("UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?")->execute($update_params);
}

// Son giriş zamanını güncelle
$pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$user['id']]);

// Başarılı giriş sonrası profile yönlendir
header("Location: profile.php");
exit;
