<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT id, name, slug FROM districts");
$districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "ID List:\n";
foreach($districts as $d) {
    echo "- ID: {$d['id']}, Name: {$d['name']}, Slug: {$d['slug']}\n";
}
?>
