<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=rehber_db", "root", "21212121");
    echo "Connection successful!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
