<?php
require_once 'config.php';

// Simulate EXACT requests the mobile app sends
$base = "http://localhost:8080/SON/laravel_api/public/api/v1/businesses";
$districts = [3, 5]; // Cermik, Cungus
$categories = ['hotel', 'restaurant'];

$results = [];

foreach ($districts as $dId) {
    foreach ($categories as $cat) {
        $url = "$base?district_id=$dId&category=$cat";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $data = json_decode($resp, true);
        $count = isset($data['data']) ? count($data['data']) : 0;
        
        $results[] = [
            "district" => $dId,
            "category" => $cat,
            "status" => $httpCode,
            "count" => $count,
            "names" => $count > 0 ? array_column($data['data'], 'business_name') : []
        ];
    }
}

// Also test SLUG resolution
$url_slug = "$base?district_id=cermik&category=Hotel";
$resp_slug = file_get_contents($url_slug);
$data_slug = json_decode($resp_slug, true);
$results[] = [
    "test" => "Slug Resolution (cermik)",
    "count" => isset($data_slug['data']) ? count($data_slug['data']) : 0
];

echo json_encode($results, JSON_PRETTY_PRINT);
?>
