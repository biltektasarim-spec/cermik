<?php
$pdo = new PDO('mysql:host=localhost;dbname=rehber_db', 'root', '21212121');
try {
    $pdo->exec("ALTER TABLE municipal_guide ADD COLUMN district_id INT DEFAULT NULL AFTER parent_id");
    $pdo->exec("ALTER TABLE municipal_guide ADD CONSTRAINT fk_guide_district FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL");
    echo "Municipal Guide table updated.\n";
} catch (Exception $e) {
    echo "Error or already updated: " . $e->getMessage() . "\n";
}

// Also let's check businesses
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM businesses LIKE 'district_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE businesses ADD COLUMN district_id INT DEFAULT NULL AFTER id, ADD CONSTRAINT fk_biz_district FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL");
        echo "Businesses table updated with district_id.\n";
    } else {
        echo "Businesses already has district_id.\n";
    }
} catch (Exception $e) { echo $e->getMessage(); }
