<?php
require_once 'config.php';
// ID 60 yerine 59'a bağla (SELAM için)
$pdo->exec("UPDATE custom_menus SET place_id = 59 WHERE name_tr = 'SELAM' AND place_id = 60");
// Tekrar senkronize et
$pdo->exec("UPDATE places p JOIN custom_menus cm ON p.id = cm.place_id SET p.image_main = cm.image WHERE cm.image IS NOT NULL AND cm.image != ''");
echo "Data repair completed.";
?>
