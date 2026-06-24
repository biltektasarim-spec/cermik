<?php
require __DIR__ . '/laravel_api/vendor/autoload.php';
$app = require_once __DIR__ . '/laravel_api/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

// Simulate Category list fetch (like in mobile)
$districtId = 3; // Cermik
$category = 'Hotel';

$businesses = DB::table('businesses')
    ->where('district_id', $districtId)
    ->where('is_approved', 1)
    ->where(function($q) use ($category) {
        $q->where('category', $category)
          ->orWhere('category', 'LIKE', '%' . $category . '%');
    })
    ->get();

header('Content-Type: application/json');
echo json_encode($businesses, JSON_PRETTY_PRINT);
?>
