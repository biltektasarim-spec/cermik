<?php
require_once 'config.php';
header('Content-Type: text/plain');

function describe($pdo, $table) {
    echo "\n--- Table: $table ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

describe($pdo, 'announcements');
describe($pdo, 'events');
?>
