<?php
// Mocking the environment
$_GET['action'] = 'google_login';
$_POST['credential'] = 'header.' . base64_encode(json_encode(['email' => 'test@example.com', 'given_name' => 'Test', 'family_name' => 'User'])) . '.signature';

try {
    include 'api/user_auth.php';
} catch (Exception $e) {
    echo "\nCaught Exception: " . $e->getMessage();
} catch (Error $e) {
    echo "\nCaught Error: " . $e->getMessage();
}
?>
