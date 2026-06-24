<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Yetkisiz erişim.");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Değiştir</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #0a0e14; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .popup-card { background: var(--glass-bg); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 25px; padding: 30px; width: 100%; max-width: 400px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: white; display: block; box-sizing: border-box; }
        label { display: block; font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="popup-card">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--secondary);">Şifre Değiştir</h3>
        <form id="password-form">
            <label>Mevcut Şifre</label>
            <input type="password" name="current_password" required>
            
            <label>Yeni Şifre</label>
            <input type="password" name="new_password" required>
            
            <label>Yeni Şifre (Tekrar)</label>
            <input type="password" name="confirm_password" required>
            
            <button type="submit" class="btn" style="width: 100%; background: var(--secondary); color: white; border: none; padding: 12px; border-radius: 12px; font-weight: 600;">Şifreyi Güncelle</button>
        </form>
    </div>

    <script>
    document.getElementById('password-form').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('api/change_password.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            alert(res.message);
            if(res.status === 'success') {
                window.close();
            }
        })
        .catch(err => alert('İşlem sırasında bir hata oluştu.'));
    };
    </script>
</body>
</html>
