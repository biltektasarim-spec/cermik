<?php
require_once 'config.php';

// Fix places table
try {
    $placeCols = $pdo->query("SHOW COLUMNS FROM places")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('district_id', $placeCols)) {
        $pdo->exec("ALTER TABLE places ADD COLUMN district_id INT DEFAULT 1");
        echo "places.district_id added.<br>";
    } else {
        echo "places.district_id exists.<br>";
    }
} catch (Exception $e) {
    echo "places error: " . $e->getMessage() . "<br>";
}

// Fix settings table
try {
    $settingsCols = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('district_id', $settingsCols)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN district_id INT DEFAULT 0");
        echo "settings.district_id added.<br>";
    } else {
        echo "settings.district_id exists.<br>";
    }
} catch (Exception $e) {
    echo "settings error: " . $e->getMessage() . "<br>";
}

// Fix users table
try {
    $userCols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('role', $userCols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'USER'");
        echo "users.role added.<br>";
    } else {
        echo "users.role exists.<br>";
    }
    if (!in_array('district_id', $userCols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN district_id INT DEFAULT NULL");
        echo "users.district_id added.<br>";
    } else {
        echo "users.district_id exists.<br>";
    }
} catch (Exception $e) {
    echo "users error: " . $e->getMessage() . "<br>";
}

// Fix admin user
try {
    $pdo->exec("UPDATE users SET role='SUPER_ADMIN', is_active=1 WHERE email='admin@admin.com'");
    echo "Admin user updated to SUPER_ADMIN.<br>";
} catch (Exception $e) {
    echo "admin update error: " . $e->getMessage() . "<br>";
}

// Check hospitals / pharmacies district_id
$tablesToCheck = ['hospitals', 'pharmacies'];
foreach ($tablesToCheck as $tbl) {
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM $tbl")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('district_id', $cols)) {
            $pdo->exec("ALTER TABLE $tbl ADD COLUMN district_id INT DEFAULT 1");
            echo "$tbl.district_id added.<br>";
        } else {
            echo "$tbl.district_id exists.<br>";
        }
    } catch (Exception $e) {
        echo "$tbl error: " . $e->getMessage() . "<br>";
    }
}

// Check businesses district_id
try {
    $cols = $pdo->query("SHOW COLUMNS FROM businesses")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('district_id', $cols)) {
        $pdo->exec("ALTER TABLE businesses ADD COLUMN district_id INT DEFAULT 1");
        echo "businesses.district_id added.<br>";
    } else {
        echo "businesses.district_id exists.<br>";
    }
    if (!in_array('description', $cols)) {
        $pdo->exec("ALTER TABLE businesses ADD COLUMN description TEXT NULL");
        echo "businesses.description added.<br>";
    }
} catch (Exception $e) {
    echo "businesses error: " . $e->getMessage() . "<br>";
}

// Check events for is_global, global_status
try {
    $cols = $pdo->query("SHOW COLUMNS FROM events")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('is_global', $cols)) {
        $pdo->exec("ALTER TABLE events ADD COLUMN is_global TINYINT(1) DEFAULT 0");
        echo "events.is_global added.<br>";
    } else {
        echo "events.is_global exists.<br>";
    }
    if (!in_array('global_status', $cols)) {
        $pdo->exec("ALTER TABLE events ADD COLUMN global_status VARCHAR(20) DEFAULT 'PENDING'");
        echo "events.global_status added.<br>";
    } else {
        echo "events.global_status exists.<br>";
    }
    if (!in_array('status', $cols)) {
        $pdo->exec("ALTER TABLE events ADD COLUMN status VARCHAR(20) DEFAULT 'APPROVED'");
        echo "events.status added.<br>";
    } else {
        echo "events.status exists.<br>";
    }
} catch (Exception $e) {
    echo "events error: " . $e->getMessage() . "<br>";
}

echo "<br><strong>Schema fix complete! <a href='index.php'>Visit index.php</a></strong>";
?>
