<?php
require_once 'config.php';
$stmt = $pdo->query("SHOW TABLES LIKE 'check_ins'");
if ($stmt->fetch()) {
    $desc = $pdo->query("DESCRIBE check_ins")->fetchAll();
    echo json_encode($desc, JSON_PRETTY_PRINT);
} else {
    echo "NO_TABLE";
}
?>
