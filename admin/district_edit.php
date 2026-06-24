<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Sadece Super Admin girebilir
if ($_SESSION['admin_role'] !== 'SUPER_ADMIN') {
    header('Location: index.php');
    exit;
}

$msg = "";
$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM districts WHERE id = ?");
    $stmt->execute([$id]);
    $district = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$district) {
        die("İlçe bulunamadı.");
    }
} else {
    die("Geçersiz ID.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $lat = $_POST['lat'] ?? '';
    $lng = $_POST['lng'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $stmt = $pdo->prepare("UPDATE districts SET name = ?, slug = ?, lat = ?, lng = ?, is_active = ? WHERE id = ?");
    if ($stmt->execute([$name, $slug, $lat ? $lat : null, $lng ? $lng : null, $is_active, $id])) {
        $msg = "İlçe bilgileri güncellendi.";
        // Veriyi tekrar çek
        $stmt = $pdo->prepare("SELECT * FROM districts WHERE id = ?");
        $stmt->execute([$id]);
        $district = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $msg = "Hata oluştu.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlçe Düzenle - Admin Paneli</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>İlçe Düzenle</h1>
                <p><?php echo htmlspecialchars($district['name']); ?> ilçesini düzenliyorsunuz.</p>
            </div>
        </header>
        <main class="page-content">
            <?php if ($msg): ?>
                <div class="alert alert-success" style="padding:15px; background:#d4edda; margin-bottom:20px; border-radius:5px;"><?php echo $msg; ?></div>
            <?php endif; ?>
            <div class="card" style="max-width: 600px;">
                <form method="POST">
                    <div class="form-group" style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px;">İlçe Adı</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($district['name']); ?>" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div class="form-group" style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px;">Slug</label>
                        <input type="text" name="slug" value="<?php echo htmlspecialchars($district['slug']); ?>" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div class="form-group" style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px;">Enlem (Latitude)</label>
                        <input type="text" name="lat" value="<?php echo htmlspecialchars($district['lat'] ?? ''); ?>" placeholder="Örn: 38.267" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div class="form-group" style="margin-bottom:15px;">
                        <label style="display:block; margin-bottom:5px;">Boylam (Longitude)</label>
                        <input type="text" name="lng" value="<?php echo htmlspecialchars($district['lng'] ?? ''); ?>" placeholder="Örn: 39.761" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div class="form-group" style="margin-bottom:15px;">
                        <label><input type="checkbox" name="is_active" <?php echo $district['is_active'] ? 'checked' : ''; ?>> Aktif</label>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Kaydet</button>
                    <a href="districts_manage.php" class="btn" style="background:#eee; color:#333; margin-left:10px;">İptal</a>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
