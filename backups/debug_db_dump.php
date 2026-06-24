<?php
require_once 'config.php';

echo "--- Districts ---\n";
$stmt = $pdo->query("SELECT id, name, slug FROM districts");
while($row = $stmt->fetch()) {
    print_r($row);
    echo "Settings for " . $row['name'] . ":\n";
    $s = get_settings($pdo, $row['id']);
    foreach($s as $k => $v) {
        if (strpos($k, 'menu_') === 0 || strpos($k, 'hero_') === 0) {
            echo "  $k => $v\n";
        }
    }
}
?>
