<?php
$baseUrl = "http://localhost:8080/SON/laravel_api/public/api/v1";
$districts = ["cermik", "cungus"];
$categories = ["Hotel", "Restaurant"];

echo "--- BUSINESS API CHECK ---\n";
foreach ($districts as $d) {
    foreach ($categories as $c) {
        $url = "$baseUrl/businesses?district_id=$d&category=$c";
        $resp = file_get_contents($url);
        $data = json_decode($resp, true);
        $count = isset($data['data']) ? count($data['data']) : 0;
        echo "District: $d, Category: $c -> Found $count items. URL: $url\n";
    }
}

echo "\n--- PHARMACY API CHECK ---\n";
foreach ($districts as $d) {
    $url = "$baseUrl/pharmacies?district_id=$d";
    $resp = file_get_contents($url);
    $data = json_decode($resp, true);
    $pCount = isset($data['data']['pharmacies']) ? count($data['data']['pharmacies']) : 0;
    $hCount = isset($data['data']['hospitals']) ? count($data['data']['hospitals']) : 0;
    echo "District: $d -> Found $pCount pharmacies and $hCount hospitals. URL: $url\n";
}
?>
