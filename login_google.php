<?php
// Bu dosya Android uygulaması tarafından yakalanmak (intercept) için oluşturulmuştur.
// Eğer bir tarayıcıdan Google Client ID ile giriş yapılmak istenirse burası kullanılabilir.
require_once 'config.php';
header("Location: https://accounts.google.com/o/oauth2/auth?client_id=" . GOOGLE_CLIENT_ID . "&response_type=code&scope=email%20profile&redirect_uri=" . urlencode((isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/login_google_callback.php"));
exit;
