<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Silme İşlemi
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: projects.php?msg=deleted");
    exit;
}

// Ekleme/Güncelleme İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $_SESSION['error'] = "Yüklenen dosya çok büyük! Sunucu limiti (post_max_size) aşıldı.";
    } elseif (!isset($_POST['title'])) {
        @csrf_verify();
        $_SESSION['error'] = "Form verileri sunucuya ulaşmadı.";
    } else {
        csrf_verify();
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'];
        $title_en = $_POST['title_en'];
        $description = $_POST['description'];
        $description_en = $_POST['description_en'];
        $description_en = $_POST['description_en'];
        $status = $_POST['status'];
        $progress = intval($_POST['progress'] ?? 0);
        
        $image = $_POST['old_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_err = '';
            if (validate_uploaded_image($_FILES['image'], $upload_err)) {
                $upload_dir = '../uploads/projects/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                
                $filename = safe_upload_filename($_FILES['image']);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                    $image = 'uploads/projects/' . $filename;
                }
            } else {
                $_SESSION['error'] = "Görsel hatası: " . $upload_err;
            }
        }

        if (!isset($_SESSION['error'])) {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE services SET title = ?, title_en = ?, description = ?, description_en = ?, image = ?, status = ?, progress = ?, district_id = ? WHERE id = ?");
                $stmt->execute([$title, $title_en, $description, $description_en, $image, $status, $progress, $admin_district_id, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO services (title, title_en, description, description_en, image, status, progress, district_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $title_en, $description, $description_en, $image, $status, $progress, $admin_district_id]);
            }
            header("Location: projects.php?msg=success");
            exit;
        }
    }
}

$projects = $pdo->query("SELECT * FROM services WHERE $admin_filter ORDER BY status DESC, id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Proje Yönetimi - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .project-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; margin-top: 1rem; }
        .project-card { background: white; border-radius: 12px; padding: 15px; border: 1px solid #ddd; }
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .status-0 { background: #fff3cd; color: #856404; }
        .status-1 { background: #d4edda; color: #155724; }
        .form-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
        .form-card { background: white; padding: 30px; border-radius: 15px; width: 100%; max-width: 500px; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Projeler / Hizmetlerimiz</h1>
                <p>Tamamlanan ve devam eden belediye projelerini yönetin.</p>
            </div>
            <div class="header-right">
                <button class="btn btn-primary" onclick="openForm()"><i class="fa-solid fa-plus"></i> Yeni Proje Ekle</button>
            </div>
        </header>

        <main class="page-content">
            <?php if(isset($_GET['msg'])): ?>
                <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">İşlem başarıyla tamamlandı.</div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="project-grid">
                <?php foreach($projects as $p): ?>
                <div class="project-card">
                    <img src="../<?php echo $p['image'] ?: 'assets/img/project_default.jpg'; ?>" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <h3 style="margin: 0; font-size: 1.1rem;"><?php echo htmlspecialchars($p['title']); ?></h3>
                        <span class="status-badge status-<?php echo $p['status']; ?>">
                            <?php echo $p['status'] == 1 ? 'Tamamlandı' : 'Devam Ediyor (' . ($p['progress'] ?? 0) . '%)'; ?>
                        </span>
                    </div>
                    <p style="font-size: 0.85rem; color: #666; margin: 10px 0;"><?php echo mb_substr($p['description'], 0, 100); ?>...</p>
                    <div style="display: flex; gap: 5px; margin-top: 15px;">
                        <button class="btn" style="flex:1; border: 1px solid #ddd;" onclick='editProject(<?php echo json_encode($p); ?>)'><i class="fa-solid fa-pen"></i> Düzenle</button>
                        <a href="projects.php?delete=<?php echo $p['id']; ?>" class="btn" style="color: #e74c3c; border: 1px solid #ddd;" onclick="return confirm('Silmek istediğinize emin misiniz?')"><i class="fa-solid fa-trash"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Form Modal -->
    <div id="projectForm" class="form-overlay">
        <div class="form-card">
            <h2 id="formTitle">Yeni Proje Ekle</h2>
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" id="proj_id">
                <input type="hidden" name="old_image" id="proj_old_image">
                <div class="form-group">
                    <label>Proje Başlığı (TR)</label>
                    <input type="text" name="title" id="proj_title" required>
                </div>
                <div class="form-group">
                    <label>Proje Başlığı (EN)</label>
                    <input type="text" name="title_en" id="proj_title_en">
                </div>
                <div class="form-group">
                    <label>Proje Durumu</label>
                    <select name="status" id="proj_status">
                        <option value="0">Devam Ediyor</option>
                        <option value="1">Tamamlandı</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>İlerleme Yüzdesi (%)</label>
                    <input type="number" name="progress" id="proj_progress" min="0" max="100" value="0">
                </div>
                <div class="form-group">
                    <label>Açıklama (TR)</label>
                    <textarea name="description" id="proj_description" style="height: 80px;"></textarea>
                </div>
                <div class="form-group">
                    <label>Açıklama (EN)</label>
                    <textarea name="description_en" id="proj_description_en" style="height: 80px;"></textarea>
                </div>
                <div class="form-group">
                    <label>Proje Görseli</label>
                    <input type="file" name="image" id="proj_image" accept="image/*">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="flex: 2;">Kaydet</button>
                    <button type="button" class="btn" style="flex: 1; border: 1px solid #ddd;" onclick="closeForm()">İptal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openForm() {
            document.getElementById('formTitle').innerText = 'Yeni Proje Ekle';
            document.getElementById('proj_id').value = '';
            document.getElementById('proj_title').value = '';
            document.getElementById('proj_title_en').value = '';
            document.getElementById('proj_description').value = '';
            document.getElementById('proj_description_en').value = '';
            document.getElementById('proj_status').value = '0';
            document.getElementById('proj_progress').value = '0';
            document.getElementById('proj_image').value = '';
            document.getElementById('projectForm').style.display = 'flex';
        }
        function closeForm() {
            document.getElementById('projectForm').style.display = 'none';
        }
        function editProject(p) {
            document.getElementById('formTitle').innerText = 'Projeyi Düzenle';
            document.getElementById('proj_id').value = p.id;
            document.getElementById('proj_title').value = p.title;
            document.getElementById('proj_title_en').value = p.title_en || '';
            document.getElementById('proj_description').value = p.description;
            document.getElementById('proj_description_en').value = p.description_en || '';
            document.getElementById('proj_status').value = p.status;
            document.getElementById('proj_progress').value = p.progress || 0;
            document.getElementById('proj_old_image').value = p.image;
            document.getElementById('projectForm').style.display = 'flex';
        }
    </script>
</body>
</html>
