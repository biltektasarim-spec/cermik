<?php
require_once 'config.php';

try {
    // 1. Çermik Kaplıcaları (ID 56)
    $stmt = $pdo->prepare("UPDATE places SET name = 'Çermik Kaplıcaları', name_en = 'Cermik Hot Springs' WHERE id = 56");
    $stmt->execute();
    echo "ID 56 Updated.\n";

    // 2. Karakaya Barajı (ID 26)
    $stmt = $pdo->prepare("UPDATE places SET name = 'Karakaya Barajı', name_en = 'Karakaya Dam' WHERE id = 26");
    $stmt->execute();
    echo "ID 26 Updated.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
