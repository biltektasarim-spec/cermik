<?php
require_once 'api/fetch_pharmacies.php';
$res = fetchPharmacies(3); // Cermik
echo json_encode($res, JSON_PRETTY_PRINT);
?>
