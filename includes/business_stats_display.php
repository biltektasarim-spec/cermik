<?php
if (!isset($pdo) || !isset($id)) return;

// Bu ayki ve yıllık verileri çek
$month_start = date('Y-m-01 00:00:00');
$year_start  = date('Y-01-01 00:00:00');

if (!function_exists('getPublicStat')) {
    function getPublicStat($pdo, $bid, $type, $start) {
        try {
            $st = $pdo->prepare("SELECT COUNT(*) FROM business_stats WHERE business_id = ? AND event_type = ? AND created_at >= ?");
            $st->execute([$bid, $type, $start]);
            return (int)$st->fetchColumn();
        } catch (Exception $e) { return 0; }
    }
}

$vMonth = getPublicStat($pdo, $id, 'view', $month_start);
$vYear  = getPublicStat($pdo, $id, 'view', $year_start);
?>
<div class="card animate-in" style="margin-top: 20px; background: linear-gradient(135deg, rgba(0, 201, 255, 0.05), rgba(146, 254, 157, 0.05)); border: 1px solid rgba(0, 201, 255, 0.1);">
    <h3 style="margin-bottom: 12px;"><i class="fa-solid fa-chart-simple" style="color: var(--secondary);"></i> <?php echo $current_lang == 'en' ? 'Business Analytics' : 'İşletme İstatistikleri'; ?></h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <div style="text-align: center; border-right: 1px solid rgba(255,255,255,0.05);">
            <strong style="font-size: 1.4rem; color: var(--secondary); display: block;"><?php echo $vMonth; ?></strong>
            <small style="opacity: 0.7; font-size: 0.75rem;"><?php echo $current_lang == 'en' ? 'Monthly Views' : 'Bu Ay Görüntülenme'; ?></small>
        </div>
        <div style="text-align: center;">
            <strong style="font-size: 1.4rem; color: #fff; display: block;"><?php echo $vYear; ?></strong>
            <small style="opacity: 0.7; font-size: 0.75rem;"><?php echo $current_lang == 'en' ? 'Yearly Views' : 'Bu Yıl Görüntülenme'; ?></small>
        </div>
    </div>
</div>
