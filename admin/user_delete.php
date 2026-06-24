<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['id'] ?? 0;

    if ($user_id > 0) {
        try {
            $pdo->beginTransaction();

            // İlişkili verileri sil
            $pdo->prepare("DELETE FROM check_ins WHERE user_id = ?")->execute([$user_id]);
            $pdo->prepare("DELETE FROM user_visits WHERE user_id = ?")->execute([$user_id]);
            $pdo->prepare("DELETE FROM ai_chat_logs WHERE user_id = ?")->execute([$user_id]);
            $pdo->prepare("DELETE FROM submissions WHERE user_id = ?")->execute([$user_id]);
            $pdo->prepare("DELETE FROM user_badges WHERE user_id = ?")->execute([$user_id]);
            
            // Kullanıcıyı sil
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);

            $pdo->commit();
            header("Location: users.php?success=deleted");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: users.php?error=delete_failed");
            exit;
        }
    }
}

header("Location: users.php");
exit;
?>
