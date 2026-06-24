<?php
require_once 'config.php';

try {
    $sql = file_get_contents('rehber_final.sql');
    if ($sql === false) {
        die("rehber_final.sql not found.");
    }

    // Split SQL into separate statements more robustly
    $queries = preg_split("/;+(?=[^']*'([^']*'[^']*')*[^']*$)/", $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if ($query) {
            try {
                $pdo->exec($query);
            } catch (PDOException $e) {
                echo "Error in query: " . htmlspecialchars(substr($query, 0, 100)) . " ... Error: " . $e->getMessage() . "<br>";
            }
        }
    }

    echo "Finished processing queries.";
} catch (PDOException $e) {
    echo "Import failed: " . $e->getMessage();
}
?>
