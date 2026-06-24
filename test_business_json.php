<?php
define('LARAVEL_START', microtime(true));
require_once __DIR__ . '/laravel_api/vendor/autoload.php';
$app = require_once __DIR__ . '/laravel_api/bootstrap/app.php';

use App\Http\Controllers\Api\BusinessController;
use Illuminate\Http\Request;

$controller = $app->make(BusinessController::class);
$request = Request::create('/api/v1/businesses/4', 'GET');
$response = $controller->show(4);

header('Content-Type: application/json');
echo $response->getContent();
