<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Lütfen giriş yapın.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Tüm alanları doldurun.']);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'Yeni şifreler eşleşmiyor.']);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'Yeni şifre en az 6 karakter olmalıdır.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current_password, $user['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Mevcut şifreniz hatalı.']);
        exit;
    }

    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update_stmt->execute([$new_hashed_password, $user_id]);

    echo json_encode(['status' => 'success', 'message' => 'Şifreniz başarıyla değiştirildi.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Bir veritabanı hatası oluştu.']);
}
?>
