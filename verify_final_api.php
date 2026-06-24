<?php
header('Content-Type: application/json');

function test_api($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$base = "http://localhost:8080/SON/laravel_api/public/api/v1/";

$results = [];

// Test Business (Hotel) with ID 1 and ID 3
$results['hotel_id1'] = test_api($base . "businesses?district_id=1&category=hotel");
$results['hotel_id3'] = test_api($base . "businesses?district_id=3&category=hotel");

// Test Pharmacy with ID 1 and ID 3
$results['pharmacy_id1'] = test_api($base . "pharmacies?district_id=1");
$results['pharmacy_id3'] = test_api($base . "pharmacies?district_id=3");

echo json_encode($results, JSON_PRETTY_PRINT);
?>
