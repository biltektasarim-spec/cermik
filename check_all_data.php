<?php
require 'config.php';
$districts = $pdo->query("SELECT id, name FROM districts")->fetchAll(PDO::FETCH_ASSOC);

foreach ($districts as $dist) {
    echo "<h2>--- DISTRICT: " . $dist['name'] . " (ID: " . $dist['id'] . ") ---</h2>";
    
    echo "<h3>Places Categories:</h3>";
    $stmt = $pdo->prepare("SELECT category, COUNT(*) as count FROM places WHERE district_id = ? GROUP BY category");
    $stmt->execute([$dist['id']]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($res)) echo "No places found.<br>";
    else {
        echo "<ul>";
        foreach ($res as $r) echo "<li>" . ($r['category'] ?: '[Empty]') . ": " . $r['count'] . "</li>";
        echo "</ul>";
    }

    echo "<h3>Businesses Categories:</h3>";
    $stmt2 = $pdo->prepare("SELECT category, COUNT(*) as count FROM businesses WHERE district_id = ? GROUP BY category");
    $stmt2->execute([$dist['id']]);
    $res2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    if (empty($res2)) echo "No businesses found.<br>";
    else {
        echo "<ul>";
        foreach ($res2 as $r) echo "<li>" . ($r['category'] ?: '[Empty]') . ": " . $r['count'] . "</li>";
        echo "</ul>";
    }
    echo "<hr>";
}
?>
