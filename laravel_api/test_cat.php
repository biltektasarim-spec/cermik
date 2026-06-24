<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Businesses:\n";
echo json_encode(\App\Models\Business::select('category')->distinct()->pluck('category')->toArray());
echo "\nPlaces:\n";
echo json_encode(\App\Models\Place::select('category')->distinct()->pluck('category')->toArray());
