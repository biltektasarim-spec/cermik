<?php
require_once 'config.php';

$hospitals = [
    [
        'district_id' => 3, // Cermik
        'name' => 'Çermik Devlet Hastanesi',
        'name_en' => 'Cermik State Hospital',
        'address' => 'Çermik, Diyarbakır',
        'phone' => '0412 461 20 18',
        'lat' => 39.452,
        'lng' => 39.452,
        'image_main' => 'assets/img/hastane.jpg',
        'description' => 'Çermik ilçesinde hizmet veren tam teşekküllü devlet hastanesi.',
        'description_en' => 'A full-service state hospital serving the district of Cermik.'
    ],
    [
        'district_id' => 5, // Cungus
        'name' => 'Çüngüş Devlet Hastanesi',
        'name_en' => 'Cungus State Hospital',
        'address' => 'Çüngüş, Diyarbakır',
        'phone' => '0412 521 21 00',
        'lat' => 38.225,
        'lng' => 39.290,
        'image_main' => 'assets/img/hastane.jpg',
        'description' => 'Çüngüş ilçesinde hizmet veren devlet hastanesi.',
        'description_en' => 'State hospital serving the district of Cungus.'
    ]
];

try {
    foreach ($hospitals as $h) {
        $check = $pdo->prepare("SELECT id FROM hospitals WHERE name = ?");
        $check->execute([$h['name']]);
        if (!$check->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO hospitals (district_id, name, name_en, address, phone, lat, lng, image_main, description, description_en) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $h['district_id'], $h['name'], $h['name_en'], $h['address'], $h['phone'],
                $h['lat'], $h['lng'], $h['image_main'], $h['description'], $h['description_en']
            ]);
        }
    }
    echo "Hospitals seeded successfully.\n";
} catch (Exception $e) {
    echo "Error seeding: " . $e->getMessage();
}
?>
