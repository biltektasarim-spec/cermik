<?php
require_once 'config.php';
// Custom menu'da resmi olan ama places tablosunda resmi olmayanları senkronize et
$stmt = $pdo->query("SELECT place_id, image FROM custom_menus WHERE place_id IS NOT NULL AND image IS NOT NULL AND image != ''");
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;
foreach ($menus as $m) {
    // Sadece image_main boş olanları veya default olanları güncelle
    $check = $pdo->prepare("SELECT image_main FROM places WHERE id = ?");
    $check->execute([$m['place_id']]);
    $current = $check->fetchColumn();
    
    if (empty($current) || strpos($current, 'default.jpg') !== false) {
        $upd = $pdo->prepare("UPDATE places SET image_main = ? WHERE id = ?");
        $upd->execute([$m['image'], $m['place_id']]);
        $updated++;
    }
}

echo "Senkronizasyon tamamlandı. $updated kayıt güncellendi.";
?>
