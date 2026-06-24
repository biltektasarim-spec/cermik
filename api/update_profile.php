<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => __('login_required_msg')]);
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    
    // PHP Upload Hataları
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msg = __('file_upload_error');
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            $msg = 'Dosya boyutu sunucu limitlerini aşıyor.';
        }
        echo json_encode(['status' => 'error', 'message' => $msg]);
        exit;
    }

    // Dosya boyutu kontrolü (Yedek olarak)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['status' => 'error', 'message' => 'Dosya boyutu 5MB\'dan büyük olamaz.']);
        exit;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => __('only_images_allowed')]);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
    $target_dir = __DIR__ . '/../uploads/avatars/';
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_path = $target_dir . $filename;
    $db_path = 'uploads/avatars/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->execute([$db_path, $user_id]);
        echo json_encode(['status' => 'success', 'message' => __('profile_image_updated'), 'image_path' => $db_path]);
    } else {
        error_log("File upload failed. Tmp: " . $file['tmp_name'] . " Target: " . $target_path);
        echo json_encode(['status' => 'error', 'message' => __('file_upload_error')]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => __('image_not_found')]);
}
?>
