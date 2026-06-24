<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$user_id = (int)($_GET['user_id'] ?? 0);

if ($user_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz kullanıcı.']);
    exit;
}

try {
    // Ensure columns exist (optional columns may not be in older schema)
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, phone, profile_image, city, district, is_active, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.']);
        exit;
    }

    // Fetch check-ins
    $checkins = [];
    try {
        $stmt2 = $pdo->prepare("
            SELECT c.id, c.status, c.created_at, c.target_type as type, d.name as district_name,
                   COALESCE(p.name, b.business_name) as name
            FROM check_ins c
            LEFT JOIN places p ON (c.target_id = p.id AND c.target_type = 'place')
            LEFT JOIN businesses b ON (c.target_id = b.id AND c.target_type = 'business')
            LEFT JOIN districts d ON c.district_id = d.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
            LIMIT 50
        ");
        $stmt2->execute([$user_id]);
        $checkins = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('[REHBER] check_ins fetch error: ' . $e->getMessage());
    }

    // Fetch cek_gonder submissions
    $submissions = [];
    try {
        $stmt3 = $pdo->prepare("
            SELECT id, basvuru_turu, aciklama, created_at, IFNULL(process_status, 'Beklemede') as process_status
            FROM cek_gonder_forms
            WHERE user_id = ?
               OR (email IS NOT NULL AND email != '' AND email = ?)
               OR (tel_no IS NOT NULL AND tel_no != '' AND tel_no = ?)
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt3->execute([$user_id, $user['email'], $user['phone']]);
        $submissions = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('[REHBER] cek_gonder_forms fetch error: ' . $e->getMessage());
    }

    $user['checkins']     = $checkins;
    $user['submissions']  = $submissions;

    echo json_encode(['status' => 'success', 'user' => $user], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
