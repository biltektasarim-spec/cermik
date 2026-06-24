<?php
require_once 'config.php';
$category = 'Restaurant';
$stmt_s = $pdo->query("SELECT value FROM settings WHERE name = 'menu_businesses_tr'");
$val_tr = $stmt_s->fetchColumn() ?: __('restaurants');
$stmt_en = $pdo->query("SELECT value FROM settings WHERE name = 'menu_businesses_en'");
$val_en = $stmt_en->fetchColumn() ?: __('restaurants');
$title = (isset($current_lang) && $current_lang === 'en') ? $val_en : $val_tr;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çermik Rehberi - <?php echo $title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px; font-size: 0.72rem;
            font-weight: 700; letter-spacing: 0.5px;
        }
        .status-open  { background: rgba(34,197,94,0.22); color: #4ade80; border: 1px solid rgba(34,197,94,0.4); }
        .status-closed{ background: rgba(239,68,68,0.22);  color: #f87171; border: 1px solid rgba(239,68,68,0.4); }
        .hours-text { font-size: 0.75rem; color: rgba(255,255,255,0.7); margin-top: 2px; }
        .closed-overlay {
            position: absolute; inset: 0; background: rgba(0,0,0,0.55);
            display: flex; align-items: center; justify-content: center;
            border-radius: inherit; z-index: 2;
        }
        .closed-stamp {
            background: rgba(239,68,68,0.85); color: #fff;
            font-size: 0.85rem; font-weight: 800; letter-spacing: 1px;
            padding: 6px 18px; border-radius: 8px; text-transform: uppercase;
            border: 2px solid rgba(255,255,255,0.3); backdrop-filter: blur(4px);
        }
    </style>
</head>
<body data-page-context="<?php echo $title; ?>">
<?php include 'includes/theme_bg.php'; ?>
<div id="app">
    <header class="header">
        <a href="index.php" class="home-link">
            <i class="fa-solid fa-house"></i> <?php echo __('home'); ?>
        </a>
        <h1><?php echo $title; ?></h1>
    </header>

    <main class="section">
        <div id="business-list" class="menu-list">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM businesses WHERE category = ? ORDER BY business_name ASC");
            $stmt->execute([$category]);
            $businesses = $stmt->fetchAll();

            if (isset($current_lang) && $current_lang === 'en') {
                foreach ($businesses as &$b) {
                    if (!empty($b['business_name_en'])) $b['business_name'] = $b['business_name_en'];
                }
                unset($b);
            }

            // Türkiye saati (UTC+3)
            $now_ts  = time() + (3 * 3600);
            $now_day = (int)gmdate('w', $now_ts); // 0=Pazar, 6=Cumartesi
            $now_min = (int)gmdate('H', $now_ts) * 60 + (int)gmdate('i', $now_ts);

            if ($businesses) {
                foreach ($businesses as $b) {
                    $bg = $b['image_main'] ?: 'assets/img/categories/restaurants_bg.jpg';

                    // Açık/Kapalı hesaplama
                    $wh = json_decode($b['working_hours'] ?? '{}', true) ?: [];
                    $has_hours = !empty($wh) && isset($wh['days'], $wh['open'], $wh['close']);
                    $is_open   = false;
                    $hours_text = '';

                    if ($has_hours) {
                        $op  = explode(':', $wh['open']);
                        $cl  = explode(':', $wh['close']);
                        $open_min  = (int)$op[0] * 60 + (int)($op[1] ?? 0);
                        $close_min = (int)$cl[0] * 60 + (int)($cl[1] ?? 0);
                        
                        $wh_days  = array_map('intval', $wh['days']);
                        $day_open  = in_array($now_day, $wh_days);

                        if ($day_open) {
                            if ($close_min < $open_min) {
                                // Gece yarısını geçiyor (Örn: 16:00 - 02:00)
                                $is_open = ($now_min >= $open_min || $now_min < $close_min);
                            } else {
                                $is_open = ($now_min >= $open_min && $now_min < $close_min);
                            }
                        }
                        if (!$is_open) {
                            // Dünden kalan mesai var mı?
                            $yesterday = ($now_day - 1 + 7) % 7;
                            if (in_array($yesterday, $wh_days) && $close_min < $open_min) {
                                if ($now_min < $close_min) {
                                    $is_open = true;
                                    $day_open = true;
                                }
                            }
                        }

                        $hours_text = $wh['open'] . ' – ' . $wh['close'];
                    }

                    // Badge
                    if ($has_hours) {
                        if ($is_open) {
                            $badge = "<span class='status-badge status-open'><i class='fa-solid fa-circle' style='font-size:0.5rem;'></i> AÇIK</span>";
                        } else {
                            $badge = "<span class='status-badge status-closed'><i class='fa-solid fa-circle' style='font-size:0.5rem;'></i> KAPALI</span>";
                        }
                        $hours_html = "<div class='hours-text'><i class='fa-regular fa-clock'></i> {$hours_text}</div>";
                    } else {
                        $badge = '';
                        $hours_html = '';
                    }

                    // Kapalıyken koyu overlay
                    $closed_overlay = (!$is_open && $has_hours)
                        ? "<div class='closed-overlay'><div class='closed-stamp'><i class='fa-solid fa-lock'></i> Şu An Kapalı</div></div>"
                        : '';

                    echo "
                    <div class='card animate-in menu-item-bg' style=\"background-image: url('{$bg}'); position: relative;\" onclick=\"app.navigateTo('business', {$b['id']})\" data-lat=\"{$b['lat']}\" data-lng=\"{$b['lng']}\">
                        <div class='menu-card-overlay'></div>
                        {$closed_overlay}
                        <div style='display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 3;'>
                            <div style='flex:1;'>
                                <h3 style='margin-bottom: 5px; text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-size: 1.1rem; font-weight: 700;'>" . htmlspecialchars($b['business_name']) . "</h3>
                                <div style='display: flex; gap: 8px; align-items: center; flex-wrap: wrap;'>
                                    {$badge}
                                    <span class='distance-info' style='color:var(--secondary); font-weight:700; font-size:0.8rem; text-shadow: 0 1px 2px rgba(0,0,0,0.5);'></span>
                                </div>
                                {$hours_html}
                            </div>
                            <i class='fa-solid fa-chevron-right' style='color: var(--secondary); font-size: 1.2rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); position:relative; z-index:3; flex-shrink:0;'></i>
                        </div>
                    </div>
";
                }
            } else {
                echo "<p>" . __('no_restaurant_found') . "</p>";
            }
            ?>
        </div>
    </main>

    <script src="assets/js/app.js"></script>
    <script>
        app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
</div>
    <!-- Bottom Navigation -->
    <?php include 'includes/bottom_nav.php'; ?>
</body>
</html>
