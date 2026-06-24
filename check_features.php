<?php
require_once 'config.php';
try {
    echo "Check for Çermik HotSpring:\n";
    $stmt = $pdo->query("SELECT id, name, category, district_id FROM places WHERE category = 'HotSpring' AND district_id = 3");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\nCheck for Çüngüş Karakaya (Usually in places or a specific query):\n";
    // Let's see what categories exist for Çüngüş (ID 5)
    $stmt2 = $pdo->query("SELECT id, name, category FROM places WHERE district_id = 5");
    print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
