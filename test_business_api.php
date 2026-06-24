<?php
require_once 'laravel_api/public/index.php';
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BusinessController;

$controller = new BusinessController();
$request = Request::create('/api/v1/businesses', 'GET', [
    'district_id' => 3,
    'category' => 'hotel'
]);

$response = $controller->index($request);
echo "Response for District 3, Category hotel:\n";
echo $response->getContent();

$request2 = Request::create('/api/v1/businesses', 'GET', [
    'district_id' => 5,
    'category' => 'hotel'
]);

$response2 = $controller->index($request2);
echo "\n\nResponse for District 5, Category hotel:\n";
echo $response2->getContent();
?>
