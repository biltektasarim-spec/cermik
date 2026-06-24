<?php
require_once 'config.php';

$cookie_content = file_get_contents('çerez.txt');
$kvkk_html = file_get_contents('privacy_policy.php');
$kvkk_text = strip_tags($kvkk_html);

try {
    $stmt = $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('kvkk_text', ?, 0) ON DUPLICATE KEY UPDATE value = ?");
    $stmt->execute([$kvkk_text, $kvkk_text]);

    $stmt = $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('cookie_policy', ?, 0) ON DUPLICATE KEY UPDATE value = ?");
    $stmt->execute([$cookie_content, $cookie_content]);

    echo "Başarıyla güncellendi.";
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
unlink(__FILE__);
?>
