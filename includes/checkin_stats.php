<?php
/**
 * Check-in Stats Component
 * Displays daily, monthly, and yearly check-in counts for the current district.
 * Included at the bottom of pages.
 */

// If district_id is not set, try to get it from session or cookie
$st_district_id = $district_id ?? ($_SESSION['district_id'] ?? ($_COOKIE['district_id'] ?? 0));

if ($st_district_id > 0) {
    $filter_sql = "";
    $count_params = [$st_district_id];
    
    if (isset($target_id) && !empty($target_id) && isset($target_type)) {
        $filter_sql = " AND target_id = ? AND target_type = ? ";
        $count_params[] = $target_id;
        $count_params[] = $target_type;
    }

    // Daily count (Combined Member + Passive)
    try {
        $q_day = "SELECT 
                    (SELECT COUNT(*) FROM check_ins WHERE district_id = ? AND status = 'APPROVED' AND DATE(created_at) = CURDATE() $filter_sql) +
                    (SELECT COUNT(*) FROM passive_stats WHERE district_id = ? AND DATE(created_at) = CURDATE() $filter_sql) 
                  AS total";
        $stmt_day = $pdo->prepare($q_day);
        $stmt_day->execute(array_merge($count_params, $count_params));
        $count_day = $stmt_day->fetchColumn();
    } catch (Exception $e) {
        $stmt_day = $pdo->prepare("SELECT COUNT(*) FROM check_ins WHERE district_id = ? AND status = 'APPROVED' AND DATE(created_at) = CURDATE() $filter_sql");
        $stmt_day->execute($count_params);
        $count_day = $stmt_day->fetchColumn();
    }

    // Monthly count
    try {
        $q_month = "SELECT 
                    (SELECT COUNT(*) FROM check_ins WHERE district_id = ? AND status = 'APPROVED' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) $filter_sql) +
                    (SELECT COUNT(*) FROM passive_stats WHERE district_id = ? AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) $filter_sql) 
                  AS total";
        $stmt_month = $pdo->prepare($q_month);
        $stmt_month->execute(array_merge($count_params, $count_params));
        $count_month = $stmt_month->fetchColumn();
    } catch (Exception $e) {
        $stmt_month = $pdo->prepare("SELECT COUNT(*) FROM check_ins WHERE district_id = ? AND status = 'APPROVED' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) $filter_sql");
        $stmt_month->execute($count_params);
        $count_month = $stmt_month->fetchColumn();
    }

    // Yearly count
    try {
        $q_year = "SELECT 
                    (SELECT COUNT(*) FROM check_ins WHERE district_id = ? AND status = 'APPROVED' AND YEAR(created_at) = YEAR(CURRENT_DATE()) $filter_sql) +
                    (SELECT COUNT(*) FROM passive_stats WHERE district_id = ? AND YEAR(created_at) = YEAR(CURRENT_DATE()) $filter_sql) 
                  AS total";
        $stmt_year = $pdo->prepare($q_year);
        $stmt_year->execute(array_merge($count_params, $count_params));
        $count_year = $stmt_year->fetchColumn();
    } catch (Exception $e) {
        $stmt_year = $pdo->prepare("SELECT COUNT(*) FROM check_ins WHERE district_id = ? AND status = 'APPROVED' AND YEAR(created_at) = YEAR(CURRENT_DATE()) $filter_sql");
        $stmt_year->execute($count_params);
        $count_year = $stmt_year->fetchColumn();
    }

    // Last 5 visitors for today
    $stmt_5_day = $pdo->prepare("SELECT u.profile_image FROM check_ins c JOIN users u ON c.user_id = u.id WHERE c.district_id = ? AND c.status = 'APPROVED' AND DATE(c.created_at) = CURDATE() $filter_sql ORDER BY c.created_at DESC LIMIT 5");
    $stmt_5_day->execute($count_params);
    $last_5_day = $stmt_5_day->fetchAll();

    // Last 5 visitors for this month
    $stmt_5_month = $pdo->prepare("SELECT u.profile_image FROM check_ins c JOIN users u ON c.user_id = u.id WHERE c.district_id = ? AND c.status = 'APPROVED' AND MONTH(c.created_at) = MONTH(CURRENT_DATE()) AND YEAR(c.created_at) = YEAR(CURRENT_DATE()) $filter_sql ORDER BY c.created_at DESC LIMIT 5");
    $stmt_5_month->execute($count_params);
    $last_5_month = $stmt_5_month->fetchAll();
} else {
    $count_day = $count_month = $count_year = 0;
    $last_5_day = $last_5_month = [];
}
?>

<div class="card" style="margin: 20px 0; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 15px;">
    <h3 style="font-size: 0.9rem; margin-bottom: 12px; opacity: 0.7; text-align: center;">
        <i class="fa-solid fa-chart-line"></i> <?php echo __('visit_stats'); ?>
    </h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; text-align: center;">
        <div style="background: rgba(255,255,255,0.03); padding: 10px 8px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div style="font-size: 1.2rem; font-weight: 700; color: var(--secondary);"><?php echo number_format($count_day); ?> <span style="font-size: 0.7rem; font-weight: 400; opacity: 0.8;">kişi</span></div>
            <div style="font-size: 0.65rem; text-transform: uppercase; opacity: 0.6; margin-bottom: 5px;"><?php echo __('daily'); ?></div>
            <?php if ($last_5_day): ?>
                <div style="display: flex; justify-content: center; margin-top: 2px;">
                    <?php foreach ($last_5_day as $idx => $v): 
                        $v_img = $v['profile_image'];
                        $isSubDir = (strpos($_SERVER['PHP_SELF'], '/cermik/') !== false || strpos($_SERVER['PHP_SELF'], '/cungus/') !== false);
                        $prefix = $isSubDir ? '../' : '';
                        $img_src = ($v_img && (strpos($v_img, 'http') === 0)) ? $v_img : (($v_img && file_exists($prefix . $v_img)) ? $prefix . $v_img : $prefix . 'assets/img/default-avatar.png');
                    ?>
                        <img src="<?php echo $img_src; ?>" style="width: 18px; height: 18px; border-radius: 50%; border: 1.5px solid #1a1a1a; object-fit: cover; margin-left: <?php echo $idx === 0 ? '0' : '-8px'; ?>; z-index: <?php echo 5 - $idx; ?>;">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div style="background: rgba(255,255,255,0.03); padding: 10px 8px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div style="font-size: 1.2rem; font-weight: 700; color: #a29bfe;"><?php echo number_format($count_month); ?> <span style="font-size: 0.7rem; font-weight: 400; opacity: 0.8;">kişi</span></div>
            <div style="font-size: 0.65rem; text-transform: uppercase; opacity: 0.6; margin-bottom: 5px;"><?php echo __('monthly'); ?></div>
            <?php if ($last_5_month): ?>
                <div style="display: flex; justify-content: center; margin-top: 2px;">
                    <?php foreach ($last_5_month as $idx => $v): 
                         $v_img = $v['profile_image'];
                         $isSubDir = (strpos($_SERVER['PHP_SELF'], '/cermik/') !== false || strpos($_SERVER['PHP_SELF'], '/cungus/') !== false);
                         $prefix = $isSubDir ? '../' : '';
                         $img_src = ($v_img && (strpos($v_img, 'http') === 0)) ? $v_img : (($v_img && file_exists($prefix . $v_img)) ? $prefix . $v_img : $prefix . 'assets/img/default-avatar.png');
                    ?>
                        <img src="<?php echo $img_src; ?>" style="width: 18px; height: 18px; border-radius: 50%; border: 1.5px solid #1a1a1a; object-fit: cover; margin-left: <?php echo $idx === 0 ? '0' : '-8px'; ?>; z-index: <?php echo 5 - $idx; ?>;">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div style="background: rgba(255,255,255,0.03); padding: 10px 8px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div style="font-size: 1.2rem; font-weight: 700; color: #fab1a0;"><?php echo number_format($count_year); ?> <span style="font-size: 0.7rem; font-weight: 400; opacity: 0.8;">kişi</span></div>
            <div style="font-size: 0.65rem; text-transform: uppercase; opacity: 0.6;"><?php echo __('yearly'); ?></div>
        </div>
</div>

<?php 
// Today's Recent Check-ins for the district OR current page
if ($st_district_id > 0) {
    $filter_sql = "";
    $params = [$st_district_id];
    
    if (isset($target_id) && !empty($target_id) && isset($target_type)) {
        $filter_sql = " AND c.target_id = ? AND c.target_type = ? ";
        $params[] = $target_id;
        $params[] = $target_type;
    }

    $stmt_recent = $pdo->prepare("
        SELECT u.first_name, u.last_name, u.profile_image, c.target_type, c.target_id, c.created_at,
               CASE WHEN c.target_type = 'place' THEN p.name ELSE b.business_name END as target_name
        FROM check_ins c 
        JOIN users u ON c.user_id = u.id 
        LEFT JOIN places p ON (c.target_type = 'place' AND c.target_id = p.id)
        LEFT JOIN businesses b ON (c.target_type = 'business' AND c.target_id = b.id)
        WHERE c.district_id = ? AND c.status = 'APPROVED' AND DATE(c.created_at) = CURDATE()
        $filter_sql
        GROUP BY u.id
        ORDER BY MAX(c.created_at) DESC LIMIT 15
    ");
    $stmt_recent->execute($params);
    $recent_checkins = $stmt_recent->fetchAll();
    
    if ($recent_checkins): ?>
    <div class="card" style="margin-top: 15px; background: rgba(255,255,255,0.02);">
        <h4 style="font-size: 0.8rem; margin-bottom: 15px; opacity: 0.8; display: flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-users-viewfinder" style="color:var(--secondary);"></i> <?php echo __('today_visitors'); ?>
        </h4>
        <div style="display: flex; gap: 12px; overflow-x: auto; padding-bottom: 10px;">
            <?php foreach ($recent_checkins as $rc): ?>
            <div style="text-align: center; min-width: 65px;">
                <div style="position: relative; display: inline-block;">
                    <?php 
                    $isSubDir = (strpos($_SERVER['PHP_SELF'], '/cermik/') !== false || strpos($_SERVER['PHP_SELF'], '/cungus/') !== false);
                    $prefix = $isSubDir ? '../' : '';
                    $v_img = $rc['profile_image'];
                    if ($v_img && (strpos($v_img, 'http') === 0)) {
                        $avatar = $v_img;
                    } else {
                        $avatar = ($v_img && file_exists($prefix . $v_img)) ? $prefix . $v_img : $prefix . 'assets/img/default-avatar.png';
                    }
                    ?>
                    <img src="<?php echo $avatar; ?>" style="width: 45px; height: 45px; border-radius: 50%; border: 2px solid var(--secondary); object-fit: cover;">
                    <span style="position: absolute; bottom: -2px; right: -2px; background: var(--secondary); color: #000; width: 16px; height: 16px; border-radius: 50%; font-size: 0.6rem; display: flex; align-items: center; justify-content: center; border: 1.5px solid #000;">
                        <i class="fa-solid fa-location-dot"></i>
                    </span>
                </div>
                <p style="font-size: 0.6rem; color: #fff; margin-top: 5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70px;">
                    <?php echo htmlspecialchars($rc['first_name'] . ' ' . mb_substr($rc['last_name'], 0, 1, 'UTF-8') . '.'); ?>
                </p>
                <p style="font-size: 0.55rem; color: var(--text-secondary);"><?php echo date('H:i', strtotime($rc['created_at'])); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; 
}
?>
