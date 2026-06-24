<?php
$pdo = new PDO('mysql:host=localhost;dbname=rehber_db', 'root', '21212121');
try {
    $pdo->exec("ALTER TABLE places MODIFY COLUMN category ENUM('Historical','Nature','Park','HotSpring','ParkAndGarden','Kuruyemis')");
    echo "Added Kuruyemis to category enum.\n";
    
    // Check if it already exists for Cermik (district_id = 3)
    $stmt = $pdo->prepare("SELECT id FROM places WHERE name LIKE ? AND district_id = 3");
    $stmt->execute(['%Kuruyemiş Pazarı%']);
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO places (district_id, name, category, description) VALUES (3, ?, 'Kuruyemis', '')")
            ->execute(['Kuruyemiş Pazarı']);
        echo "Inserted Kuruyemiş Pazarı for Çermik.\n";
    }

    // Check if it already exists for Cungus (district_id = 5)
    $stmt = $pdo->prepare("SELECT id FROM places WHERE name LIKE ? AND district_id = 5");
    $stmt->execute(['%Kuruyemiş Pazarı%']);
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO places (district_id, name, category, description) VALUES (5, ?, 'Kuruyemis', '')")
            ->execute(['Kuruyemiş Pazarı']);
        echo "Inserted Kuruyemiş Pazarı for Çüngüş.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
