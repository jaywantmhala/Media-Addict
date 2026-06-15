<?php
// db_config.php

// Disable displaying PHP errors in the HTTP response to avoid breaking JSON outputs
ini_set('display_errors', 0);
error_reporting(0);

// Auto-detect environment: local vs production
$is_local = false;
$http_host = $_SERVER['HTTP_HOST'] ?? '';
$remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';

if (
    php_sapi_name() === 'cli' ||
    $http_host === 'localhost' || 
    $http_host === '127.0.0.1' || 
    strpos($http_host, 'localhost:') === 0 ||
    $remote_addr === '127.0.0.1' || 
    $remote_addr === '::1'
) {
    $is_local = true;
}

if ($is_local) {
    // Local Database Configuration
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', 'root'); // Match your local MySQL password
    define('DB_NAME', 'kiran');
} else {
    // Production Database Configuration (Hostinger / Live Server)
    define('DB_HOST', 'srv1000.hstgr.io'); // or use IP: 82.25.121.95
    define('DB_USER', 'u345349733_hellokiran_usr');
    define('DB_PASS', 'Kiran@#98765');
    define('DB_NAME', 'u345349733_hellokiran_db');
}

/**
 * Establish and return a mysqli connection to the database.
 * Gracefully handles errors by outputting valid JSON and exiting.
 * 
 * @param bool $select_db Whether to select the database immediately on connection
 * @return mysqli
 */
function get_db_connection($select_db = true) {
    // Disable mysqli exception throwing so we can handle connection errors manually
    mysqli_report(MYSQLI_REPORT_OFF);
    
    $host = DB_HOST;
    $user = DB_USER;
    $pass = DB_PASS;
    $name = DB_NAME;
    
    try {
        if ($select_db) {
            $conn = @new mysqli($host, $user, $pass, $name);
        } else {
            $conn = @new mysqli($host, $user, $pass);
        }
        
        if ($conn->connect_error) {
            throw new Exception($conn->connect_error);
        }
        
        return $conn;
    } catch (Exception $e) {
        // Output clean JSON error response
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            "status" => "error",
            "message" => "Database connection failed. Please check credentials in db_config.php.",
            "details" => $e->getMessage()
        ]);
        exit();
    }
}
?>
