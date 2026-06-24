<?php
require_once 'config.php';
try {
    $pdo->exec("ALTER TABLE places ADD COLUMN heading_hastaliklar_tr VARCHAR(255) DEFAULT NULL");
    $pdo->exec("ALTER TABLE places ADD COLUMN heading_hastaliklar_en VARCHAR(255) DEFAULT NULL");
    echo "Migration successful: heading_hastaliklar columns added.";
} catch (Exception $e) {
    echo "Migration warning: " . $e->getMessage() . " (Perhaps columns already exist?)";
}
?>
