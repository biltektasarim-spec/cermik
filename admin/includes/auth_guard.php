<?php
/**
 * Admin paneli ortak auth guard.
 * Tüm admin sayfaları bu dosyayı include etmeli.
 *
 * - Oturum açık mı kontrolü
 * - 2 saatlik oturum zaman aşımı (hareketsizlikte)
 */

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ' . (defined('ADMIN_LOGIN_PATH') ? ADMIN_LOGIN_PATH : 'login.php'));
    exit;
}

// Oturum zaman aşımı: 2 saat hareketsizlik
define('ADMIN_SESSION_TIMEOUT', 7200);

if (isset($_SESSION['admin_logged_at'])) {
    if ((time() - $_SESSION['admin_logged_at']) > ADMIN_SESSION_TIMEOUT) {
        // Oturumu sonlandır
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: login.php?reason=timeout');
        exit;
    }
}
// Her istekte aktivite zamanını güncelle
$_SESSION['admin_logged_at'] = time();

// --- Role-Based Filtering Global Logic ---
$is_super_admin = ($_SESSION['admin_role'] === 'SUPER_ADMIN');
$admin_district_id = intval($_SESSION['admin_district_id'] ?? 0);

// DISTRICT_ADMIN can only see their own district
if (!$is_super_admin) {
    if (!isset($_SESSION['district_admin_locked'])) {
        $stmt_u = $pdo->prepare("SELECT district_id FROM users WHERE id = ?");
        $stmt_u->execute([$_SESSION['admin_user_id']]);
        $u_data = $stmt_u->fetch();
        $_SESSION['admin_district_id'] = $u_data ? $u_data['district_id'] : 0;
        $_SESSION['district_admin_locked'] = true;
    }
    $admin_district_id = $_SESSION['admin_district_id'];
}

if ($is_super_admin) {
    if ($admin_district_id > 0) {
        $admin_filter = "district_id = $admin_district_id";
        $admin_query_val = $admin_district_id;
    } else {
        $admin_filter = "1=1"; // Tüm veriler
        $admin_query_val = null;
    }
} else {
    $admin_filter = "district_id = $admin_district_id";
    $admin_query_val = $admin_district_id;
}

// Security: Prevent DISTRICT_ADMIN from accessing districts_manage.php
if (!$is_super_admin && basename($_SERVER['PHP_SELF']) == 'districts_manage.php') {
    header('Location: index.php');
    exit;
}
// -----------------------------------------
