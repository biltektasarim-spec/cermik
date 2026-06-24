<?php
header('Content-Type: text/plain; charset=utf-8');

// Include Laravel bootstrap to access DB
require __DIR__ . '/laravel_api/vendor/autoload.php';
$app = require_once __DIR__ . '/laravel_api/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

echo "--- PRODUCTS SCHEMA ---\n";
$schema = DB::select("DESCRIBE products");
foreach ($schema as $s) {
    echo "Field: {$s->Field} | Type: {$s->Type}\n";
}
echo "\n";
echo "--- DISTRICTS ---\n";
$districts = DB::table('districts')->get();
foreach ($districts as $d) {
    echo "ID: {$d->id} | Name: {$d->name} | Slug: {$d->slug}\n";
}

echo "--- DISTRICTS SCHEMA ---\n";
$columnsD = DB::select("SHOW COLUMNS FROM districts");
foreach ($columnsD as $column) {
    echo "Field: {$column->Field} | Type: {$column->Type}\n";
}

echo "--- BUSINESSES SCHEMA ---\n";
$columns = DB::select("SHOW COLUMNS FROM businesses");
foreach ($columns as $column) {
    echo "Field: {$column->Field} | Type: {$column->Type}\n";
}

echo "\n--- LIVE_BROADCASTS SCHEMA ---\n";
$columnsLb = DB::select("SHOW COLUMNS FROM live_broadcasts");
foreach ($columnsLb as $column) {
    echo "Field: {$column->Field} | Type: {$column->Type}\n";
}

echo "\n--- PLACES SCHEMA ---\n";
$columnsPl = DB::select("SHOW COLUMNS FROM places");
foreach ($columnsPl as $column) {
    echo "Field: {$column->Field} | Type: {$column->Type}\n";
}

echo "\n--- PHARMACIES SCHEMA ---\n";
$columnsP = DB::select("SHOW COLUMNS FROM pharmacies");
foreach ($columnsP as $column) {
    echo "Field: {$column->Field} | Type: {$column->Type}\n";
}

echo "\n--- PHARMACIES (District ID 3 - Cermik) ---\n";
$pharmacies = DB::table('pharmacies')->where('district_id', 3)->get();
echo "Count: " . count($pharmacies) . "\n";

echo "\n--- PHARMACIES (District ID 5 - Cungus) ---\n";
$pharmacies5 = DB::table('pharmacies')->where('district_id', 5)->get();
echo "Count: " . count($pharmacies5) . "\n";

echo "--- BUSINESSES (District ID 3 - Cermik) ---\n";
$bizs = DB::table('businesses')->where('district_id', 3)->get();
echo "Count: " . count($bizs) . "\n";
foreach ($bizs as $b) {
    echo "ID: {$b->id} | Name: {$b->business_name}\n";
}

echo "\n--- BUSINESSES (District ID 5 - Cungus) ---\n";
$bizs = DB::table('businesses')->where('district_id', 5)->get();
echo "Count: " . count($bizs) . "\n";
foreach ($bizs as $b) {
    echo "- Name: {$b->business_name} | Image: {$b->image_main} | Gallery: {$b->image_gallery}\n";
}

echo "\n--- END ---\n";
?>
