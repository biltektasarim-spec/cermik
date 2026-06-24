<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $baslik = $_POST['baslik'] ?? '';
    $icerik = $_POST['icerik'] ?? '';

    if (!empty($baslik) && !empty($icerik)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO genel_bilgiler (baslik, icerik) VALUES (?, ?)");
            $stmt->execute([$baslik, $icerik]);
            $message = "Bilgi başarıyla kaydedildi.";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Hata oluştu: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "Lütfen tüm alanları doldurun.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genel Bilgi Ekle - Çermik Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Genel Bilgi Ekle</h1>
                <p style="color: var(--text-muted);">Chatbot için yeni yerel bilgiler ekleyin.</p>
            </div>
            <div class="header-right">
                <a href="genel_bilgiler.php" class="btn btn-secondary"><i class="fa-solid fa-list"></i> Listeye Dön</a>
            </div>
        </header>

        <main class="page-content">
            <?php if ($message): ?>
                <div style="background: <?php echo $messageType == 'success' ? '#e8f5e9' : '#ffebee'; ?>; 
                            color: <?php echo $messageType == 'success' ? '#2e7d32' : '#c62828'; ?>; 
                            padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid <?php echo $messageType == 'success' ? '#c8e6c9' : '#ffcdd2'; ?>;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form action="" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem; padding: 1rem;">
                    <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label for="baslik" style="font-weight: 600;">Başlık</label>
                        <input type="text" name="baslik" id="baslik" required placeholder="Örn: Çermik Kaplıcaları Tarihçesi" 
                               style="padding: 0.8rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem;">
                    </div>
                    
                    <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label for="icerik" style="font-weight: 600;">İçerik</label>
                        <textarea name="icerik" id="icerik" rows="10" required placeholder="İlçenin kaplıcaları Roma döneminden beri..." 
                                  style="padding: 0.8rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; font-family: inherit; resize: vertical;"></textarea>
                    </div>

                    <div style="margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary" style="padding: 0.8rem 2rem;">
                            <i class="fa-solid fa-save"></i> Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>
