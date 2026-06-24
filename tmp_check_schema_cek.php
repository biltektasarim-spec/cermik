<?php
require_once 'config.php';
$stmt = $pdo->query("SHOW COLUMNS FROM cek_gonder_forms");
while($r = $stmt->fetch()) {
    echo $r['Field'] . ' (' . $r['Type'] . ')' . PHP_EOL;
}
?>
