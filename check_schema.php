<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("DESCRIBE municipal_guide");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in municipal_guide:\n";
    print_r($columns);
    
    $stmt2 = $pdo->query("DESCRIBE live_broadcasts");
    echo "\nColumns in live_broadcasts:\n";
    print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));

    $stmt3 = $pdo->query("DESCRIBE custom_menus");
    echo "\nColumns in custom_menus:\n";
    print_r($stmt3->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
