<?php
require_once 'config.php';
try {
    $stmt1 = $pdo->query("SELECT DISTINCT category FROM places");
    echo "Existing Categories in PLACES:\n";
    print_r($stmt1->fetchAll(PDO::FETCH_ASSOC));
    
    $stmt2 = $pdo->query("SELECT DISTINCT category FROM businesses");
    echo "\nExisting Categories in BUSINESSES:\n";
    print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
