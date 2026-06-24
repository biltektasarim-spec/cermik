<?php
// Test script to verify DistrictController output with dynamic ID
define('LARAVEL_START', microtime(true));
require __DIR__.'/laravel_api/vendor/autoload.php';
$app = require_once __DIR__.'/laravel_api/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$id = isset($_GET['id']) ? $_GET['id'] : 3;

$request = Illuminate\Http\Request::create("/api/v1/districts/$id", 'GET');
$response = $kernel->handle($request);
header('Content-Type: application/json');
echo $response->getContent();
?>
