<?php
require __DIR__ . '/laravel_api/vendor/autoload.php';
$app = require_once __DIR__ . '/laravel_api/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());
use Illuminate\Support\Facades\DB;

echo "--- PRODUCTS SAMPLES ---\n";
$products = DB::table('products')->whereNotNull('image_path')->limit(5)->get();
foreach ($products as $p) {
    echo "ID: {$p->id} | Name: {$p->name} | Image: {$p->image_path}\n";
}
?>
