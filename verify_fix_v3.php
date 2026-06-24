<?php
header('Content-Type: text/plain; charset=utf-8');

$api_base = "http://localhost:8080/SON/laravel_api/public/api/v1";

function check_url($url, $label) {
    echo "Testing $label: $url\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $data = json_decode($response, true);
        if (isset($data['status']) && $data['status'] == 'success') {
            $count = 0;
            if (isset($data['data'])) {
                if (isset($data['data']['pharmacies'])) {
                    $count = count($data['data']['pharmacies']) + count($data['data']['hospitals']);
                } else if (is_array($data['data'])) {
                    $count = count($data['data']);
                } else {
                    $count = 1; // single object like district
                }
            }
            echo "✅ SUCCESS (HTTP 200). Items found: $count\n";
        } else {
            echo "❌ API ERROR: " . ($data['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "❌ HTTP FAILURE ($http_code)\n";
        echo "Response snippet: " . substr($response, 0, 200) . "...\n";
    }
    echo "--------------------------------------------------\n";
}

// 1. Test District Show (Legacy ID 3)
check_url("$api_base/districts/3", "District Cermik (Legacy ID 3)");

// 2. Test Businesses (Cermik Hotel - Legacy ID 3)
check_url("$api_base/businesses?district_id=3&category=hotel", "Businesses Cermik Hotel (Legacy ID 3)");

// 3. Test Pharmacies (Cermik - Legacy ID 3)
check_url("$api_base/pharmacies?district_id=3", "Pharmacies Cermik (Legacy ID 3)");

// 4. Test Duty Pharmacies (Cermik - Legacy ID 3)
check_url("$api_base/pharmacies/duty?district_id=3", "Duty Pharmacies Cermik (Legacy ID 3)");

// 5. Test Canonical ID 1 for comparison
check_url("$api_base/pharmacies?district_id=1", "Pharmacies Cermik (Canonical ID 1)");

echo "\nIf all ✅ SUCCESS, the mobile app should now display data correctly.\n";
?>
