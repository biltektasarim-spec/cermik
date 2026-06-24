<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
    exit;
}

try {
    // 1. User basic info (to ensure ID is valid)
    $stmt_user = $pdo->prepare("SELECT first_name, last_name, email, phone, profile_image FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit;
    }

    // 2. Counts
    $stmt_checkin_count = $pdo->prepare("SELECT COUNT(*) as count FROM check_ins WHERE user_id = ? AND status = 'APPROVED'");
    $stmt_checkin_count->execute([$user_id]);
    $checkin_count = $stmt_checkin_count->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt_cek_gonder_count = $pdo->prepare("SELECT COUNT(*) as count FROM cek_gonder_forms WHERE user_id = ?");
    $stmt_cek_gonder_count->execute([$user_id]);
    $cek_gonder_count = $stmt_cek_gonder_count->fetch(PDO::FETCH_ASSOC)['count'];

    // 3. Recent Check-ins
    // We fetch names from both places and businesses
    $stmt_checkins = $pdo->prepare("
        SELECT c.id, c.status, c.created_at, c.target_type,
               COALESCE(p.name, b.business_name) as name,
               d.name as district_name
        FROM check_ins c
        LEFT JOIN places p ON (c.target_id = p.id AND c.target_type = 'place')
        LEFT JOIN businesses b ON (c.target_id = b.id AND c.target_type = 'business')
        LEFT JOIN districts d ON c.district_id = d.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
        LIMIT 20
    ");
    $stmt_checkins->execute([$user_id]);
    $checkins = $stmt_checkins->fetchAll(PDO::FETCH_ASSOC);

    // 4. Recent Cek Gonder Submissions
    $stmt_subs = $pdo->prepare("
        SELECT id, basvuru_turu, aciklama, created_at
        FROM cek_gonder_forms
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt_subs->execute([$user_id]);
    $submissions = $stmt_subs->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'stats' => [
            'approved_checkins' => (int)$checkin_count,
            'cek_gonder_submissions' => (int)$cek_gonder_count
        ],
        'history' => [
            'checkins' => $checkins,
            'submissions' => $submissions
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
ob_end_flush();
