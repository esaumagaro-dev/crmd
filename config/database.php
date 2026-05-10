<?php
/**
 * Database Configuration
 * Cybersecurity Risk Management Dashboard
 * Update credentials to match your local MySQL setup
 */

// Database connection parameters
$db_host = 'localhost';      // Database host
$db_name = 'crmd_db';        // Database name (from database.sql)
$db_user = 'root';           // MySQL username (default for XAMPP/WAMP)
$db_pass = '';               // MySQL password (empty by default in XAMPP/WAMP)

// PDO Connection
try {
    $pdo = new PDO(
        'mysql:host=' . $db_host . ';dbname=' . $db_name . ';charset=utf8mb4',
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Database Connection Failed: ' . htmlspecialchars($e->getMessage()));
}
?>
