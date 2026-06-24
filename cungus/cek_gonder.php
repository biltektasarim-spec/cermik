<?php 
require_once '../config.php'; 
$district_id = $_SESSION['district_id'] ?? ($_COOKIE['district_id'] ?? 0);
$settings = get_settings($pdo, $district_id);

$u_name = '';
$u_email = '';
$u_phone = '';
$u_readonly = '';

if (isset($_SESSION['user_id'])) {
    $stmtUser = $pdo->prepare("SELECT first_name, last_name, email, phone FROM users WHERE id = ?");
    $stmtUser->execute([$_SESSION['user_id']]);
    $user = $stmtUser->fetch();
    if ($user) {
        $u_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $u_email = $user['email'] ?? '';
        $u_phone = $user['phone'] ?? '';
        $u_readonly = 'readonly style="opacity: 0.7; cursor: not-allowed; background: rgba(0,0,0,0.2);"';
    }
}

$cek_gonder_title = $settings['menu_cek_gonder_tr'] ?? __('cek_gonder');
if(isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'en' && isset($settings['menu_cek_gonder_en'])) {
    $cek_gonder_title = $settings['menu_cek_gonder_en'];
}

$default_types = [
    ['id' => 'Bilgilendirme', 'icon' => 'fa-circle-info', 'color' => '#3498db', 'label' => __('info_type'), 'active' => true],
    ['id' => 'İstek', 'icon' => 'fa-hand', 'color' => '#9b59b6', 'label' => __('request_type'), 'active' => true],
    ['id' => 'Öneri', 'icon' => 'fa-lightbulb', 'color' => '#f39c12', 'label' => __('suggestion_type'), 'active' => true],
    ['id' => 'Şikayet', 'icon' => 'fa-triangle-exclamation', 'color' => '#e74c3c', 'label' => __('complaint_type'), 'active' => true],
    ['id' => 'Teşekkür', 'icon' => 'fa-heart', 'color' => '#e91e63', 'label' => __('thanks_type'), 'active' => true],
];
$basvuru_tipleri = $default_types;
if (isset($settings['cek_gonder_types']) && !empty($settings['cek_gonder_types'])) {
    $parsed = json_decode($settings['cek_gonder_types'], true);
    if(is_array($parsed) && count($parsed) > 0) {
        $basvuru_tipleri = [];
        $is_en = (isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'en');
        foreach($parsed as $pt) {
            $pt['label'] = $is_en ? ($pt['en'] ?? $pt['tr']) : ($pt['tr'] ?? $pt['en']);
            $basvuru_tipleri[] = $pt;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($cek_gonder_title); ?> | <?php echo __('belediye_rehberi'); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-field {
            margin-bottom: 16px;
        }
        .form-field label {
            display: block;
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 6px;
            font-weight: 600;
        }
        .form-field input,
        .form-field textarea,
        .form-field select {
            width: 100%;
            padding: 13px 16px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: white;
            font-size: 0.95rem;
            font-family: var(--font-main);
            outline: none;
            transition: border-color 0.3s, background 0.3s;
        }
        .form-field select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.5)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 18px;
            cursor: pointer;
            padding-right: 45px;
        }
        .form-field select option { background: #121826; color: white; }
        .form-field input:focus,
        .form-field textarea:focus,
        .form-field select:focus {
            border-color: var(--secondary);
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 4px rgba(0,201,255,0.1);
        }
        .form-field textarea { resize: vertical; min-height: 100px; }

        /* Başvuru Türü Seçimi */
        .basvuru-turu-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .basvuru-turu-btn {
            padding: 12px 8px;
            border-radius: 14px;
            border: 2px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.04);
            color: var(--text-secondary);
            cursor: pointer;
            font-family: var(--font-main);
            font-size: 0.88rem;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            transition: all 0.25s ease;
        }
        .basvuru-turu-btn i { font-size: 1.4rem; }
        .basvuru-turu-btn:hover {
            border-color: rgba(0,201,255,0.4);
            color: white;
            background: rgba(0,201,255,0.08);
        }
        .basvuru-turu-btn.active {
            border-color: var(--secondary);
            background: rgba(0,201,255,0.15);
            color: var(--secondary);
        }
        /* Son buton (teşekkür) tam genişlik olsun */
        .basvuru-turu-grid .full-width { grid-column: 1 / -1; }

        /* Fotoğraf Yükleme */
        .photo-upload-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        .photo-upload-box {
            aspect-ratio: 1;
            border: 2px dashed rgba(255,255,255,0.2);
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            background: rgba(255,255,255,0.04);
            transition: all 0.3s ease;
        }
        .photo-upload-box:hover { border-color: var(--secondary); background: rgba(0,201,255,0.06); }
        .photo-upload-box input[type="file"] {
            position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer;
        }
        .photo-upload-box .upload-icon { font-size: 1.8rem; color: var(--secondary); margin-bottom: 5px; }
        .photo-upload-box .upload-label { font-size: 0.7rem; color: var(--text-secondary); font-weight: 600; }
        .photo-upload-box .preview-img {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; border-radius: 12px;
        }
        .photo-upload-box .remove-photo {
            position: absolute; top: 5px; right: 5px; width: 22px; height: 22px;
            background: rgba(255,0,0,0.7); border-radius: 50%; border: none; color: white;
            font-size: 0.7rem; cursor: pointer; display: none; align-items: center; justify-content: center;
            z-index: 10;
        }

        /* Başarı Mesajı */
        #success-msg {
            display: none;
            text-align: center;
            padding: 30px;
        }
        #success-msg .success-icon {
            font-size: 4rem;
            color: #27ae60;
            margin-bottom: 20px;
            animation: bounceIn 0.6s ease;
        }
        @keyframes bounceIn {
            0% { transform: scale(0.5); opacity: 0; }
            70% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Section ayırıcı */
        .form-section-title {
            font-size: 0.8rem;
            color: var(--secondary);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(0,201,255,0.2);
        }
    </style>
</head>
<body data-page-context="Çek Gönder Başvuru">
<?php include '../includes/theme_bg.php'; ?>
<div id="app">
    <header class="header">
        <a href="index.php" class="home-link">
            <i class="fa-solid fa-house"></i> <?php echo __('home'); ?>
        </a>
        <h1><?php echo $settings['site_name'] ?? 'Çüngüş'; ?></h1>
    </header>

    <div style="background: linear-gradient(135deg, rgba(26,107,60,0.85), rgba(39,174,96,0.7)),
        url('../assets/img/bg/bg_default.jpg'); background-size: cover; background-position: center;
        padding: 30px 20px 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.08);">
        <i class="fa-solid fa-paper-plane" style="font-size: 2.5rem; color: #2ecc71; margin-bottom: 10px;"></i>
        <h2 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 8px; color: white;"><?php echo htmlspecialchars($cek_gonder_title); ?></h2>
        <p style="color: rgba(255,255,255,0.8); font-size: 0.9rem;"><?php echo __('cek_gonder_desc'); ?></p>
    </div>

    <main class="section animate-in" style="padding-top: 20px;">

        <!-- Başvuru Formu -->
        <div class="card animate-in" id="form-card">

            <!-- BAŞVURU TÜRÜ -->
            <div class="form-section-title"><i class="fa-solid fa-list-check"></i> <?php echo __('submission_type'); ?></div>
            <input type="hidden" id="basvuru_turu" name="basvuru_turu" value="">
            <div class="basvuru-turu-grid">
                <?php 
                $count = 0;
                $total_active = count(array_filter($basvuru_tipleri, function($t) { return $t['active']; }));
                foreach($basvuru_tipleri as $tur): 
                    if(!isset($tur['active']) || !$tur['active']) continue;
                    $count++;
                    $is_last_odd = ($count == $total_active && $total_active % 2 != 0);
                    $full_class = $is_last_odd ? 'full-width' : '';
                ?>
                <button type="button" class="basvuru-turu-btn <?php echo $full_class; ?>" onclick="selectTur('<?php echo htmlspecialchars($tur['id']); ?>', this)">
                    <i class="fa-solid <?php echo htmlspecialchars($tur['icon']); ?>" style="color: <?php echo htmlspecialchars($tur['color']); ?>;"></i>
                    <?php echo htmlspecialchars($tur['label']); ?>
                </button>
                <?php endforeach; ?>
            </div>
            <p id="tur-error" style="color: #e74c3c; font-size: 0.8rem; display: none; margin-bottom: 12px;">
                <i class="fa-solid fa-exclamation-circle"></i> <?php echo __('type_select_error'); ?>
            </p>

            <!-- KİŞİSEL BİLGİLER -->
            <div class="form-section-title" style="margin-top: 10px;"><i class="fa-solid fa-user"></i> <?php echo __('personal_info'); ?></div>
            <div class="form-field">
                <label><?php echo __('fullname_label'); ?> *</label>
                <input type="text" id="ad_soyad" placeholder="<?php echo __('fullname_placeholder'); ?>" maxlength="100" value="<?php echo htmlspecialchars($u_name); ?>" <?php echo $u_name ? $u_readonly : ''; ?>>
            </div>
            <div class="form-field">
                <label><?php echo __('tc_no_label'); ?> *</label>
                <input type="text" id="tc_no" placeholder="<?php echo __('tc_no_placeholder'); ?>" maxlength="11"
                    pattern="[0-9]{11}" inputmode="numeric"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11)">
            </div>
            <div class="form-field">
                <label><?php echo __('email_label'); ?></label>
                <input type="email" id="email" placeholder="ornek@mail.com" value="<?php echo htmlspecialchars($u_email); ?>" <?php echo $u_email ? $u_readonly : ''; ?>>
            </div>
            <div class="form-field">
                <label><?php echo __('tel_no_label'); ?></label>
                <input type="tel" id="tel_no" placeholder="05XX XXX XX XX" maxlength="15" inputmode="numeric" value="<?php echo htmlspecialchars($u_phone); ?>" <?php echo $u_phone ? $u_readonly : ''; ?>>
            </div>

            <!-- AÇIKLAMA -->
            <div class="form-section-title" style="margin-top: 10px;"><i class="fa-solid fa-message"></i> <?php echo __('description_label'); ?></div>
            <div class="form-field">
                <label><?php echo __('description_label'); ?> *</label>
                <textarea id="aciklama" placeholder="<?php echo __('desc_placeholder'); ?>" rows="4"></textarea>
            </div>

            <!-- FOTOĞRAF EKLEME -->
            <div class="form-section-title" style="margin-top: 10px;"><i class="fa-solid fa-images"></i> <?php echo __('add_photo_limit'); ?></div>
            <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 12px;">
                <?php echo __('photo_upload_hint'); ?>
            </p>
            <div class="photo-upload-grid">
                <!-- Fotoğraf 1 -->
                <div class="photo-upload-box" id="box1" onclick="document.getElementById('foto1').click()">
                    <input type="file" id="foto1" accept="image/*" onchange="previewPhoto(this, 'box1')" onclick="event.stopPropagation()">
                    <i class="fa-solid fa-camera upload-icon"></i>
                    <span class="upload-label"><?php echo __('photo_label'); ?> 1</span>
                    <button class="remove-photo" id="remove1" type="button" onclick="removePhoto('box1', 'foto1', 'remove1'); event.stopPropagation();">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <!-- Fotoğraf 2 -->
                <div class="photo-upload-box" id="box2" onclick="document.getElementById('foto2').click()">
                    <input type="file" id="foto2" accept="image/*" onchange="previewPhoto(this, 'box2')" onclick="event.stopPropagation()">
                    <i class="fa-solid fa-camera upload-icon"></i>
                    <span class="upload-label"><?php echo __('photo_label'); ?> 2</span>
                    <button class="remove-photo" id="remove2" type="button" onclick="removePhoto('box2', 'foto2', 'remove2'); event.stopPropagation();">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <!-- Fotoğraf 3 -->
                <div class="photo-upload-box" id="box3" onclick="document.getElementById('foto3').click()">
                    <input type="file" id="foto3" accept="image/*" onchange="previewPhoto(this, 'box3')" onclick="event.stopPropagation()">
                    <i class="fa-solid fa-camera upload-icon"></i>
                    <span class="upload-label"><?php echo __('photo_label'); ?> 3</span>
                    <button class="remove-photo" id="remove3" type="button" onclick="removePhoto('box3', 'foto3', 'remove3'); event.stopPropagation();">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- GÖNDER BUTONU -->
            <button class="btn btn-primary" style="margin-top: 25px;" onclick="submitForm()" id="submit-btn">
                <i class="fa-solid fa-paper-plane"></i> <?php echo __('submit_btn'); ?>
            </button>
            <p id="form-error" style="color: #e74c3c; font-size: 0.85rem; display: none; margin-top: 10px; text-align: center;"></p>
        </div>

        <!-- Başarı Mesajı (gönderim sonrası görünür) -->
        <div class="card animate-in" id="success-msg">
            <div class="success-icon"><i class="fa-solid fa-circle-check"></i></div>
            <h3 style="font-size: 1.4rem; margin-bottom: 10px;"><?php echo __('submission_received_title'); ?></h3>
            <p style="color: var(--text-secondary);"><?php echo __('submission_received_msg'); ?></p>
            <button class="btn btn-primary" style="margin-top: 25px;" onclick="window.location.href='index.php'">
                <i class="fa-solid fa-house"></i> <?php echo __('return_home'); ?>
            </button>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <?php include '../includes/bottom_nav.php'; ?>
    <script src="../assets/js/app.js?v=7.0"></script>
    <script>
        app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
    <script>
    let selectedTur = '';

    function selectTur(tur, btn) {
        selectedTur = tur;
        document.getElementById('basvuru_turu').value = tur;
        document.querySelectorAll('.basvuru-turu-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tur-error').style.display = 'none';
    }

    function previewPhoto(input, boxId) {
        const box = document.getElementById(boxId);
        const removeBtn = box.querySelector('.remove-photo');
        const existingPreview = box.querySelector('.preview-img');
        if (existingPreview) existingPreview.remove();

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.className = 'preview-img';
                img.src = e.target.result;
                box.appendChild(img);
                box.querySelector('.upload-icon').style.display = 'none';
                box.querySelector('.upload-label').style.display = 'none';
                removeBtn.style.display = 'flex';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removePhoto(boxId, inputId, removeBtnId) {
        const box = document.getElementById(boxId);
        const preview = box.querySelector('.preview-img');
        if (preview) preview.remove();
        document.getElementById(inputId).value = '';
        document.getElementById(removeBtnId).style.display = 'none';
        box.querySelector('.upload-icon').style.display = '';
        box.querySelector('.upload-label').style.display = '';
    }

    function submitForm() {
        const errorEl = document.getElementById('form-error');
        errorEl.style.display = 'none';

        // Validasyon
        if (!selectedTur) {
            document.getElementById('tur-error').style.display = 'block';
            document.querySelector('.basvuru-turu-grid').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        const adSoyad = document.getElementById('ad_soyad').value.trim();
        const tcNo    = document.getElementById('tc_no').value.trim();
        const aciklama= document.getElementById('aciklama').value.trim();

        if (!adSoyad || !tcNo || !aciklama) {
            errorEl.textContent = '<?php echo __('validation_error'); ?>';
            errorEl.style.display = 'block';
            return;
        }
        if (tcNo.length !== 11) {
            errorEl.textContent = '<?php echo __('tc_error_11'); ?>';
            errorEl.style.display = 'block';
            return;
        }

        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <?php echo __('submitting'); ?>';

        const formData = new FormData();
        formData.append('basvuru_turu', selectedTur);
        formData.append('district_id', '<?php echo $district_id; ?>');
        formData.append('ad_soyad', adSoyad);
        formData.append('tc_no', tcNo);
        formData.append('email', document.getElementById('email').value.trim());
        formData.append('tel_no', document.getElementById('tel_no').value.trim());
        formData.append('aciklama', aciklama);

        const foto1 = document.getElementById('foto1');
        const foto2 = document.getElementById('foto2');
        const foto3 = document.getElementById('foto3');
        if (foto1.files[0]) formData.append('foto1', foto1.files[0]);
        if (foto2.files[0]) formData.append('foto2', foto2.files[0]);
        if (foto3.files[0]) formData.append('foto3', foto3.files[0]);

        fetch('../api/save_cek_gonder.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                document.getElementById('form-card').style.display = 'none';
                document.getElementById('success-msg').style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                errorEl.textContent = res.message || '<?php echo __('server_error'); ?>';
                errorEl.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <?php echo __('submit_btn'); ?>';
            }
        })
        .catch(() => {
            errorEl.textContent = '<?php echo __('server_conn_error'); ?>';
            errorEl.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <?php echo __('submit_btn'); ?>';
        });
    }
    </script>
</div>
</body>
</html>
