<?php
// Mocking the call to the internal Laravel API for district detail
$districtId = 5; // Çüngüş
$url = "http://localhost:8080/SON/laravel_api/public/api/v1/districts/$districtId";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
header('Content-Type: application/json');
echo $response;
?>
