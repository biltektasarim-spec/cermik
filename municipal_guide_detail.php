<?php
require_once 'config.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM municipal_guide WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: index.php");
    exit;
}

// Fetch children
$stmt_children = $pdo->prepare("SELECT * FROM municipal_guide WHERE parent_id = ? ORDER BY sort_order ASC, id ASC");
$stmt_children->execute([$id]);
$children = $stmt_children->fetchAll();

// Fetch parent for back link
$parent = null;
if ($item['parent_id']) {
    $stmt_p = $pdo->prepare("SELECT * FROM municipal_guide WHERE id = ?");
    $stmt_p->execute([$item['parent_id']]);
    $parent = $stmt_p->fetch();
}

$is_en = ($current_lang === 'en');
$display_title = ($is_en && !empty($item['title_en'])) ? $item['title_en'] : $item['title'];
$display_desc = ($is_en && !empty($item['description_en'])) ? $item['description_en'] : $item['description'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($display_title); ?> - <?php echo __('belediye_rehberi'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .guide-detail-header {
            padding: 40px 20px;
            text-align: center;
            background: linear-gradient(180deg, rgba(0, 136, 204, 0.2) 0%, transparent 100%);
        }
        .guide-main-img {
            width: 100%;
            max-height: 250px;
            object-fit: cover;
            border-radius: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .guide-content {
            padding: 20px;
        }
        .sub-menu-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .sub-menu-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            border: 1px solid var(--glass-bg);
            text-decoration: none;
            color: white;
            transition: transform 0.2s;
        }
        .sub-menu-card:active { transform: scale(0.95); }
        .sub-menu-card i { font-size: 1.5rem; color: var(--secondary); margin-bottom: 10px; display: block; }
    </style>
</head>
<body>
<?php include 'includes/theme_bg.php'; ?>

<div id="app">
    <header class="header">
        <a href="<?php echo $parent ? 'municipal_guide_detail.php?id='.$parent['id'] : 'index.php'; ?>" class="home-link">
            <i class="fa-solid fa-chevron-left"></i> <?php echo __('back'); ?>
        </a>
        <h1><?php echo __('municipal_guide'); ?></h1>
        <div class="header-icons">
            <i class="fa-solid fa-share-nodes"></i>
        </div>
    </header>

    <main class="animate-in">
        <div class="guide-detail-header">
            <?php if($item['image']): ?>
                <img src="<?php echo $item['image']; ?>" class="guide-main-img" alt="<?php echo htmlspecialchars($display_title); ?>">
            <?php endif; ?>
            <h2 style="font-size: 1.8rem; margin-bottom: 5px;"><?php echo htmlspecialchars($display_title); ?></h2>
            <div style="width: 50px; height: 3px; background: var(--secondary); margin: 15px auto;"></div>
        </div>

        <div class="guide-content">
            <div class="card">
                <p style="white-space: pre-wrap; color: var(--text-primary); line-height: 1.8;">
                    <?php echo htmlspecialchars($display_desc ?: ($item['description'] ?: __('guide_desc_empty'))); ?>
                </p>
            </div>

            <?php if(count($children) > 0): ?>
                <h3 style="margin-top: 30px; font-size: 1.1rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;"><?php echo __('sub_sections'); ?></h3>
                <div class="sub-menu-grid">
                    <?php foreach($children as $child): ?>
                        <a href="municipal_guide_detail.php?id=<?php echo $child['id']; ?>" class="sub-menu-card">
                            <i class="fa-solid fa-folder-open"></i>
                            <span style="font-size: 0.9rem; font-weight: 600;"><?php echo htmlspecialchars(($is_en && !empty($child['title_en'])) ? $child['title_en'] : $child['title']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>



    <nav class="nav-bar">
        <div class="nav-item" onclick="window.location.href='index.php'">
            <i class="fa-solid fa-house"></i>
            <span><?php echo __('home'); ?></span>
        </div>
        <div class="nav-item" onclick="window.location.href='index.php?tab=events'">
            <i class="fa-solid fa-calendar-days"></i>
            <span><?php echo __('event_tab'); ?></span>
        </div>
        <div class="nav-item" onclick="window.location.href='index.php?tab=services'">
            <i class="fa-solid fa-hand-holding-heart"></i>
            <span><?php echo __('service_tab'); ?></span>
        </div>
    </nav>
</div>

<script src="assets/js/app.js"></script>
<script>
    app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
    <!-- Bottom Navigation -->
    <?php include 'includes/bottom_nav.php'; ?>
</body>
</html>
