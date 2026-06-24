<?php
// Simulating the API call directly within the Laravel environment if possible, 
// but easier to just check the DB with the same logic.
require_once 'config.php';

$district_id = 3; // Cermik
$category = 'hotel';

try {
    // Exact logic from BusinessController.php
    $sql = "SELECT id, business_name, category, is_approved, is_active FROM businesses WHERE district_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$district_id]);
    $all = $stmt->fetchAll();

    $filtered = [];
    foreach($all as $b) {
        $matches = false;
        $cat = strtolower($b['category']);
        if ($cat == 'hotel') $matches = true;
        
        // This is what the API does:
        if ($b['is_approved'] == 1 && $matches) {
            $filtered[] = $b;
        }
    }

    echo json_encode([
        'district_id' => $district_id,
        'category' => $category,
        'total_in_db' => count($all),
        'passed_filters' => count($filtered),
        'samples' => $filtered
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo $e->getMessage();
}
?>
