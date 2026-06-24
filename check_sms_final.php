<?php
require_once 'config.php';
$keys = ['sms_api_id', 'sms_api_key', 'sms_title'];
foreach ($keys as $k) {
    $stmt = $pdo->prepare("SELECT * FROM settings WHERE name = ?");
    $stmt->execute([$k]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Key: $k | Row Exists: " . ($row ? "Yes" : "No") . " | Value: " . ($row ? $row['value'] : "N/A") . "\n";
}
// Also check columns
$stmt = $pdo->query("DESCRIBE settings");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\n--- Columns ---\n";
foreach ($cols as $c) {
    echo $c['Field'] . "\n";
}
?>
