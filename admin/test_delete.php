<?php
require_once '../config.php';
// Simulate auth_guard variables if needed, or just include it
// For testing, we'll manually set the variables
$admin_filter = "1=1"; 

$delete_id = 9999; // Non-existent ID for safety

try {
    echo "Testing DELETE query with admin_filter: $admin_filter\n";
    $sql = "DELETE FROM businesses WHERE id = ? AND ($admin_filter)";
    echo "SQL: $sql\n";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$delete_id]);
    echo "Success! Row count: " . $stmt->rowCount() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
