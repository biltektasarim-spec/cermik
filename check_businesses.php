<?php
require_once 'config.php';
try {
    echo "--- CHECKING BUSINESSES FOR DISTRICT 3 (CERMIK) ---\n";
    $stmt = $pdo->prepare("SELECT category, COUNT(*) as count FROM businesses WHERE district_id = 3 GROUP BY category");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- CHECKING TABLES SCHEMA ---";
    $stmt2 = $pdo->query("SHOW TABLES");
    print_r($stmt2->fetchAll(PDO::FETCH_COLUMN));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
