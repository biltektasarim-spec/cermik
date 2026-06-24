<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Sadece POST ile silme yapılabilir (GET ile silme = CSRF açığı)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: businesses.php');
    exit;
}

// CSRF doğrula
csrf_verify();

$id = safe_id($_POST['id'] ?? 0);
if ($id < 1) {
    header('Location: businesses.php?msg=invalid');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM businesses WHERE id = ?');
$stmt->execute([$id]);

header('Location: businesses.php?msg=deleted');
exit;
