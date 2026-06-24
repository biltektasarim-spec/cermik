<?php
require_once 'config.php';
header('Content-Type: text/plain');

try {
    $stmt = $pdo->query("DESCRIBE settings");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
