<?php
require_once 'config.php';
// Fix charset encoding for districts
$pdo->exec("SET NAMES utf8mb4");
$pdo->exec("UPDATE districts SET name = 'Çüngüş' WHERE slug = 'cungus'");
echo "Çüngüş name fixed.<br>";

// Verify
$rows = $pdo->query("SELECT id, name, slug FROM districts")->fetchAll();
foreach ($rows as $r) {
    echo "ID: {$r['id']}, Name: {$r['name']}, Slug: {$r['slug']}<br>";
}
echo "Done.";
?>
