<?php
require_once '../config.php';
header('Content-Type: text/plain; charset=utf-8');

try {
    echo "Checking for foreign keys referencing 'businesses'...\n";
    $dbName = 'rehber_db'; // From config.php
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
            REFERENCED_TABLE_SCHEMA = ? AND
            REFERENCED_TABLE_NAME = 'businesses'
    ");
    $stmt->execute([$dbName]);
    $results = $stmt->fetchAll();
    
    if (empty($results)) {
        echo "No foreign keys found referencing 'businesses'.\n";
    } else {
        foreach ($results as $row) {
            echo "Table: " . $row['TABLE_NAME'] . " | Column: " . $row['COLUMN_NAME'] . " | Constraint: " . $row['CONSTRAINT_NAME'] . "\n";
        }
    }

    echo "\nChecking for triggers on 'businesses'...\n";
    $stmt = $pdo->prepare("SHOW TRIGGERS LIKE 'businesses'");
    $stmt->execute();
    $triggers = $stmt->fetchAll();
    if (empty($triggers)) {
        echo "No triggers found on 'businesses' table.\n";
    } else {
        foreach ($triggers as $t) {
            echo "Trigger: " . $t['Trigger'] . " | Event: " . $t['Event'] . " | Timing: " . $t['Timing'] . "\n";
        }
    }

    echo "\nChecking PHP Error Log path...\n";
    echo "error_log: " . ini_get('error_log') . "\n";
    echo "display_errors: " . ini_get('display_errors') . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
