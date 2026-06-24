<?php
/**
 * Shared Visit Display Component
 * Expects: $target_id, $target_type (place|business), $target_name, $pdo
 */

if (!isset($_SESSION['user_id'])) {
    return; // Sadece giriş yapmış kullanıcılara göster
}

$current_user_id = $_SESSION['user_id'];
$user_visit_count = 0;
$user_last_visit  = null;

// Bu hedef için ziyaret verilerini çek
$stmt_cnt = $pdo->prepare("SELECT COUNT(*) FROM user_visits WHERE user_id = ? AND target_id = ? AND target_type = ?");
$stmt_cnt->execute([$current_user_id, $target_id, $target_type]);
$user_visit_count = (int)$stmt_cnt->fetchColumn();

// Son ziyaret tarihi
$stmt_lv = $pdo->prepare("SELECT visit_date FROM user_visits WHERE user_id = ? AND target_id = ? AND target_type = ? ORDER BY id DESC LIMIT 1");
$stmt_lv->execute([$current_user_id, $target_id, $target_type]);
$user_last_visit = $stmt_lv->fetchColumn();
?>

<!-- Üye Ziyaret Bilgisi -->
<div class="card animate-in" style="border-left: 4px solid #f6ad55; margin-top: 15px;">
    <div style="display:flex; align-items:center; gap:12px;">
        <div style="background:rgba(246,173,85,0.15); border-radius:12px; padding:12px; flex-shrink:0;">
            <i class="fa-solid fa-map-location-dot" style="font-size:1.6rem; color:#f6ad55;"></i>
        </div>
        <div>
            <?php if ($user_visit_count <= 1): ?>
                <h4 style="margin:0 0 4px; font-size:0.95rem;"><?php echo __('first_visit'); ?> 🎉</h4>
                <p style="margin:0; font-size:0.82rem; color:var(--text-secondary);">
                    <b><?php echo htmlspecialchars($target_name); ?></b> <?php echo __('visit_no_first'); ?>
                </p>
            <?php else: ?>
                <h4 style="margin:0 0 4px; font-size:0.95rem;">
                    <?php 
                    $visit_text = __('visit_no_count');
                    $visit_text = str_replace('.', '<strong style="color:#f6ad55;">' . $user_visit_count . '.</strong>', $visit_text);
                    echo $visit_text;
                    ?>
                </h4>
                <p style="margin:0; font-size:0.82rem; color:var(--text-secondary);">
                    <?php echo __('prev_visit'); ?>: <?php echo $user_last_visit ? date('d.m.Y H:i', strtotime($user_last_visit)) : '—'; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
