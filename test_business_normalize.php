require_once __DIR__ . '/laravel_api/vendor/autoload.php';
$app = require_once __DIR__ . '/laravel_api/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/api/v1/businesses/4', 'GET')
);
echo $response->getContent();
