<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Paylaşım - Çermik</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        input, textarea { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 12px; border: 1px solid var(--glass-bg); background: var(--card-bg); color: white; }
    </style>
</head>
<body>
<?php include 'includes/theme_bg.php'; ?>
<div id="app">
    <header class="header">
        <a href="profile.php" style="color: white;"><i class="fa-solid fa-arrow-left"></i></a>
        <h1>Sizden Gelenler</h1>
    </header>

    <main class="section animate-in">
        <div class="card">
            <h3>Paylaşım Formu</h3>
            <p style="margin-bottom: 20px; font-weight: bold; color: var(--accent); padding: 15px; background: rgba(246, 173, 85, 0.1); border-radius: 8px; border-left: 4px solid var(--accent); line-height: 1.5;">Belediyemiz tarafından ilçemizin çeşitli turizm alanlarında çekilen resimler aylık, yıllık olarak değerlendirmeye alınıp sosyal medya hesaplarımızdan paylaşılmaktadır.</p>

            <form action="api/save_submission.php" method="POST" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Başlık" required>
                <textarea name="content" placeholder="Açıklama veya Hikaye" style="height: 150px;" required></textarea>
                <label style="display: block; margin-bottom: 10px; color: var(--text-secondary);">Görsel Seç (opsiyonel)</label>
                <input type="file" name="image" accept="image/*">
                <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Gönder</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
