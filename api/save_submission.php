<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$title   = trim($_POST['title']   ?? '');
$content = trim($_POST['content'] ?? '');

if ($title === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Başlık boş olamaz.']);
    exit;
}

// 500 karakter üst sınır
if (mb_strlen($title) > 200 || mb_strlen($content) > 5000) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Girdi çok uzun.']);
    exit;
}

$image_path = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $upload_error = '';
    if (!validate_uploaded_image($_FILES['image'], $upload_error)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $upload_error]);
        exit;
    }

    $target_dir = '../uploads/submissions/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $filename    = safe_upload_filename($_FILES['image']);
    $target_file = $target_dir . $filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Dosya yüklenemedi.']);
        exit;
    }

    $image_path = 'uploads/submissions/' . $filename;
}

try {
    $stmt = $pdo->prepare('INSERT INTO submissions (user_id, title, content, image_path) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user_id, $title, $content, $image_path]);
    header('Location: ../profile.php?msg=success');
} catch (\PDOException $e) {
    error_log('[REHBER submission] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası.']);
}
