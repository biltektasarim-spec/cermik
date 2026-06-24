<?php
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch broadcast
$stmt = $pdo->prepare("SELECT * FROM live_broadcasts WHERE id = ? AND is_active = 1");
$stmt->execute([$id]);
$broadcast = $stmt->fetch();

if (!$broadcast) {
    header("Location: index.php");
    exit;
}

$is_en = ($current_lang === 'en');
$b_title = ($is_en && !empty($broadcast['title_en'])) ? $broadcast['title_en'] : $broadcast['title'];
$b_desc = ($is_en && !empty($broadcast['description_en'])) ? $broadcast['description_en'] : $broadcast['description'];
$b_img = !empty($broadcast['image']) ? $broadcast['image'] : 'assets/img/og-default.jpg';

// Fetch district settings for header/footer if linked to a specific district
$settings = [];
if ($broadcast['district_id'] > 0) {
    $settings = get_settings($pdo, $broadcast['district_id']);
    $stmt_d = $pdo->prepare("SELECT name FROM districts WHERE id = ?");
    $stmt_d->execute([$broadcast['district_id']]);
    $district_name = $stmt_d->fetchColumn();
} else {
    $settings = get_settings($pdo, 0); // Global settings
    $district_name = "Tüm İlçeler";
}

// Determine video URL
$video_iframe = '';
if (!empty($broadcast['youtube_url'])) {
    $parsed_url = parse_url($broadcast['youtube_url']);
    $video_id = '';
    if (isset($parsed_url['query'])) {
        parse_str($parsed_url['query'], $query_params);
        if (isset($query_params['v'])) {
            $video_id = $query_params['v'];
        }
    } elseif (isset($parsed_url['path'])) {
        $path_parts = explode('/', trim($parsed_url['path'], '/'));
        $video_id = end($path_parts);
    }
    if ($video_id) {
        $video_iframe = '<iframe width="100%" height="100%" src="https://www.youtube.com/embed/' . htmlspecialchars($video_id) . '?autoplay=1" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%;"></iframe>';
    }
} elseif (!empty($broadcast['facebook_url'])) {
     $encoded_fb_url = urlencode($broadcast['facebook_url']);
     $video_iframe = '<iframe src="https://www.facebook.com/plugins/video.php?href=' . $encoded_fb_url . '&show_text=false&width=auto" width="100%" height="100%" style="border:none;overflow:hidden; position:absolute; top:0; left:0; width:100%; height:100%;" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $is_en ? 'en' : 'tr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($b_title); ?> - Canlı Yayın</title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Style -->
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
    <style>
        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            height: 0;
            overflow: hidden;
            background: #000;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .content-card {
            background: rgba(30, 41, 59, 0.8);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
        }
        .live-badge {
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <?php include 'includes/theme_bg.php'; ?>
    <div id="app">
        <!-- Header -->
        <header class="header">
            <a href="district.php?slug=<?php echo htmlspecialchars($slug); ?>" class="circle-btn" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h1 style="font-size: 1.1rem; margin:0; text-align:center; flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; padding:0 15px;">
                <?php echo htmlspecialchars($b_title); ?>
            </h1>
            <div style="width:40px;"></div> <!-- spacer -->
        </header>

        <main id="main-content" style="padding: 20px;">
            <div class="animate-in" 
                 data-lat="<?php echo $broadcast['lat']; ?>" 
                 data-lng="<?php echo $broadcast['lng']; ?>" 
                 data-id="<?php echo $broadcast['id']; ?>" 
                 data-type="live">
                <!-- Video Section -->
                <div class="video-container">
                    <?php if (!empty($video_iframe)): ?>
                        <?php echo $video_iframe; ?>
                    <?php else: ?>
                        <div style="position:absolute; top:0; left:0; width:100%; height:100%; background:url('<?php echo htmlspecialchars($b_img); ?>') center/cover; opacity:0.6;"></div>
                        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); text-align:center;">
                            <i class="fa-solid fa-video-slash" style="font-size:3rem; color:rgba(255,255,255,0.5); margin-bottom:10px;"></i>
                            <p style="color:rgba(255,255,255,0.8); font-weight:600;">Yayın Akışı Bulunamadı</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info Section -->
                <div class="content-card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                        <h2 style="margin:0; font-size:1.5rem; color:white; line-height:1.3;"><?php echo htmlspecialchars($b_title); ?></h2>
                        <?php if (!empty($video_iframe)): ?>
                            <span class="live-badge"><i class="fa-solid fa-circle" style="font-size:0.6rem;"></i> CANLI</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($b_desc)): ?>
                    <div style="color:var(--text-secondary); line-height:1.6; font-size:0.95rem;">
                        <?php echo nl2br(htmlspecialchars($b_desc)); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); display:flex; flex-direction:column; gap:12px; color:var(--text-secondary); font-size:0.9rem;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <i class="fa-solid fa-location-dot" style="color:var(--primary);"></i> 
                            <span>Bölge: <?php echo htmlspecialchars($district_name); ?></span>
                        </div>
                        
                        <?php if (!empty($broadcast['lat']) && !empty($broadcast['lng'])): ?>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <i class="fa-solid fa-location-arrow" style="color:var(--secondary);"></i>
                            <span class="distance-info" style="font-weight:700; color:var(--secondary);">
                                <i class="fa-solid fa-spinner fa-spin"></i> Mesafe hesaplanıyor...
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Traffic/Map Section if lat/lng available -->
                <?php if (!empty($broadcast['lat']) && !empty($broadcast['lng'])): ?>
                    <?php 
                    $widget_lat = $broadcast['lat'];
                    $widget_lng = $broadcast['lng'];
                    $widget_name = $b_title;
                    include 'includes/traffic_widget.php';
                    ?>
                    
                    <div style="text-align:right; margin-top:-10px; margin-bottom:20px; padding-right:10px;">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $broadcast['lat']; ?>,<?php echo $broadcast['lng']; ?>" target="_blank" class="btn btn-primary" style="padding: 8px 15px; font-size:0.85rem; border-radius:10px;">
                            <i class="fa-solid fa-location-arrow"></i> Yol Tarifi Al
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>
    <script src="assets/js/app.js?v=7.1"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof app !== 'undefined') app.init();
        });
    </script>
</body>
</html>
