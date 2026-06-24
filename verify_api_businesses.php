<?php
$baseUrl = "http://localhost:8080/SON/laravel_api/public/api/v1/businesses";

$tests = [
    "Cermik_Hotel" => "$baseUrl?district_id=3&category=Hotel",
    "Cermik_Restaurant" => "$baseUrl?district_id=3&category=Restaurant",
    "Cungus_Hotel" => "$baseUrl?district_id=5&category=Hotel",
    "Cungus_Restaurant" => "$baseUrl?district_id=5&category=Restaurant"
];

$results = [];
foreach ($tests as $key => $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $results[$key] = json_decode($response, true);
    curl_close($ch);
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>
