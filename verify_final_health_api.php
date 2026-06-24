<?php
$baseUrl = "http://localhost:8080/SON/laravel_api/public/api/v1/pharmacies";
$tests = [
    "Cermik_Pharmacy" => "$baseUrl?district_id=cermik",
    "Cungus_Pharmacy" => "$baseUrl?district_id=cungus",
];

foreach ($tests as $name => $url) {
    $resp = file_get_contents($url);
    $data = json_decode($resp, true);
    $pCount = isset($data['data']['pharmacies']) ? count($data['data']['pharmacies']) : 0;
    $hCount = isset($data['data']['hospitals']) ? count($data['data']['hospitals']) : 0;
    echo "$name: Found $pCount pharmacies and $hCount hospitals.\n";
}
?>
