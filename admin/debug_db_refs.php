<?php
require_once '../config.php';
header('Content-Type: text/plain; charset=utf-8');

try {
    echo "Comprehensive Foreign Key Check for 'businesses' table:\n";
    $stmt = $pdo->prepare("
        SELECT 
            TABLE_NAME, 
            COLUMN_NAME, 
            CONSTRAINT_NAME, 
            REFERENCED_TABLE_NAME, 
            REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            REFERENCED_TABLE_NAME = 'businesses'
            AND TABLE_SCHEMA = DATABASE()
    ");
    $stmt->execute();
    $refs = $stmt->fetchAll();
    
    if (empty($refs)) {
        echo "No explicit foreign keys found.\n";
    } else {
        foreach ($refs as $r) {
            echo "Table: " . $r['TABLE_NAME'] . " | Column: " . $r['COLUMN_NAME'] . " | Constraint: " . $r['CONSTRAINT_NAME'] . "\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
