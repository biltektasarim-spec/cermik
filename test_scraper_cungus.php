<?php
require_once 'api/fetch_pharmacies.php';
$res = fetchPharmacies(5); // Cungus
echo json_encode($res, JSON_PRETTY_PRINT);
?>
