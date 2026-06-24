<?php
require_once 'config.php';

try {
    // 1. Clean Çüngüş (ID: 5) Hero Title (remove junk 'asdasd')
    $stmt = $pdo->prepare("UPDATE settings SET value = 'Karakaya Barajı' WHERE district_id = 5 AND name = 'hero_title_tr'");
    $stmt->execute();

    // 2. Fix social links for Çüngüş (they were copies of Cermik)
    // For now, if we don't have accurate links, better to make them neutral or pointing to district name search
    $stmt = $pdo->prepare("UPDATE settings SET value = 'https://www.facebook.com/search/top?q=Çüngüş%20Belediyesi' WHERE district_id = 5 AND name = 'facebook_link'");
    $stmt->execute();
    $stmt = $pdo->prepare("UPDATE settings SET value = 'https://www.instagram.com/explore/tags/Çüngüş/' WHERE district_id = 5 AND name = 'instagram_link'");
    $stmt->execute();
    $stmt = $pdo->prepare("UPDATE settings SET value = '' WHERE district_id = 5 AND name = 'twitter_link'");
    $stmt->execute();

    // 3. Fix Place ID 26 (Karakaya Barajı) category - it was incorrectly set to HotSpring (causing Cermik-like logic issues)
    // We change it to 'Nature' so it's handled correctly as a natural landmark
    $stmt = $pdo->prepare("UPDATE places SET category = 'Nature' WHERE id = 26 AND district_id = 5");
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'Çüngüş data cleaned successfully.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
