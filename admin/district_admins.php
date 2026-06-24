<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Sadece Super Admin erişebilir
if ($_SESSION['admin_role'] !== 'SUPER_ADMIN') {
    header('Location: index.php');
    exit;
}

$district_id = intval($_GET['district_id'] ?? 0);
if (!$district_id) {
    header('Location: districts_manage.php');
    exit;
}

// İlçeyi bul
$stmt = $pdo->prepare("SELECT * FROM districts WHERE id = ?");
$stmt->execute([$district_id]);
$district = $stmt->fetch();

if (!$district) {
    header('Location: districts_manage.php');
    exit;
}

$success = '';
$error = '';

// Form Gönderildiyse İşlemler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($first_name && $last_name && $email && $password) {
            // Email kontrolü
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $error = 'Bu e-posta adresi zaten başka bir hesaba kayıtlı.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $insert = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, is_active, role, district_id) VALUES (?, ?, ?, ?, 1, 'DISTRICT_ADMIN', ?)");
                if ($insert->execute([$first_name, $last_name, $email, $hashed, $district_id])) {
                    $success = 'Yönetici başarıyla eklendi.';
                } else {
                    $error = 'Yönetici eklenirken bir hata oluştu.';
                }
            }
        } else {
            $error = 'Lütfen tüm alanları doldurun.';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $user_id = intval($_POST['user_id'] ?? 0);
        if ($user_id) {
            $del = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'DISTRICT_ADMIN' AND district_id = ?");
            if ($del->execute([$user_id, $district_id])) {
                $success = 'Yönetici hesabı başarıyla silindi.';
            } else {
                $error = 'Silme işleminde hata oluştu.';
            }
        }
    }
}

// Mevcut yöneticileri getir
$stmt_admins = $pdo->prepare("SELECT * FROM users WHERE role = 'DISTRICT_ADMIN' AND district_id = ? ORDER BY created_at DESC");
$stmt_admins->execute([$district_id]);
$admins = $stmt_admins->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($district['name']); ?> Yöneticileri - S.Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-group margin-bottom: 15px; 
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1><?php echo htmlspecialchars($district['name']); ?> Yöneticileri</h1>
                <p style="color: var(--text-muted);">Bu ilçeyi yönetme yetkisine sahip hesapları görüntüleyin ve yönetin.</p>
            </div>
            <div class="header-right">
                <a href="districts_manage.php" class="btn" style="background:#7f8c8d; color:white;"><i class="fa-solid fa-arrow-left"></i> İlçelere Dön</a>
            </div>
        </header>

        <main class="page-content">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
                
                <!-- Yönetici Listesi -->
                <div class="card" style="flex: 2; min-width: 300px;">
                    <h2>Mevcut Yöneticiler</h2>
                    <?php if (count($admins) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Ad Soyad</th>
                                <th>E-posta</th>
                                <th>Kayıt Tarihi</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></td>
                                <td><strong><?php echo htmlspecialchars($admin['email']); ?></strong></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($admin['created_at'])); ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Bu yönetici hesabını tamamen silmek istediğinize emin misiniz? (Tüm verileri kaybolabilir)');" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $admin['id']; ?>">
                                        <button type="submit" class="btn" style="background: #e74c3c; color: white;"><i class="fa-solid fa-trash"></i> Sil</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p style="color: #666; font-style: italic;">Bu ilçe için henüz atanmış bir yönetici bulunmuyor.</p>
                    <?php endif; ?>
                </div>

                <!-- Yönetici Ekleme Formu -->
                <div class="card" style="flex: 1; min-width: 300px; background: #fdfdfd; border-top: 4px solid #3498db;">
                    <h2>Yeni Yönetici Ekle</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Ad</label>
                            <input type="text" name="first_name" required placeholder="Örn: Ahmet">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Soyad</label>
                            <input type="text" name="last_name" required placeholder="Örn: Yılmaz">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>E-posta Adresi (Giriş için kullanılacak)</label>
                            <input type="email" name="email" required placeholder="Örn: admin@cermik.com">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Şifre</label>
                            <input type="password" name="password" required placeholder="Güçlü bir şifre girin">
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fa-solid fa-user-plus"></i> Yönetici Ekle</button>
                    </form>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
