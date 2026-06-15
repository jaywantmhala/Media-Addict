<?php
// save_booking.php

require_once 'db_config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

// Connect based on environment
if ($is_local) {
    // On local, connect without database selected first to allow creating it if it doesn't exist
    $conn = get_db_connection(false);
    $dbname = DB_NAME;
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbname`";
    if ($conn->query($sql) !== TRUE) {
        die(json_encode(["status" => "error", "message" => "Error creating database: " . $conn->error]));
    }
    if (!$conn->select_db(DB_NAME)) {
        die(json_encode(["status" => "error", "message" => "Error selecting database: " . $conn->error]));
    }
} else {
    // On production (Hostinger), connect directly with database name
    $conn = get_db_connection(true);
}

// 3. Create table if not exists
$tableSql = "CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `mobile` VARCHAR(15) NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `booking_date` DATE DEFAULT NULL,
    `payment_status` VARCHAR(50) DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($tableSql) !== TRUE) {
    die(json_encode(["status" => "error", "message" => "Error creating table: " . $conn->error]));
}

// Alter table to add booking_date column if the table existed before without it
$checkColumnQuery = "SHOW COLUMNS FROM `bookings` LIKE 'booking_date'";
$columnExistsResult = $conn->query($checkColumnQuery);
if ($columnExistsResult && $columnExistsResult->num_rows == 0) {
    $alterSql = "ALTER TABLE `bookings` ADD COLUMN `booking_date` DATE DEFAULT NULL AFTER `city`";
    $conn->query($alterSql);
}

// Get POST data sent from Javascript fetch()
$data = json_decode(file_get_contents("php://input"));

// If someone just opens this PHP file in the browser directly to test
if ($data === null) {
    echo json_encode(["status" => "success", "message" => "Database '$dbname' and table 'bookings' have been successfully created! Everything is working."]);
    $conn->close();
    exit();
}

// 4. Save the lead into the database
if (isset($data->name) && isset($data->mobile) && isset($data->city)) {
    
    // Prevent SQL Injection
    $name = $conn->real_escape_string($data->name);
    $mobile = $conn->real_escape_string($data->mobile);
    $city = $conn->real_escape_string($data->city);
    $booking_date = !empty($data->booking_date) ? $conn->real_escape_string($data->booking_date) : null;
    $status = "Inquiry";
    
    $stmt = $conn->prepare("INSERT INTO `bookings` (`name`, `mobile`, `city`, `booking_date`, `payment_status`) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        die(json_encode(["status" => "error", "message" => "Statement prepare failed: " . $conn->error]));
    }

    $stmt->bind_param("sssss", $name, $mobile, $city, $booking_date, $status);
    
    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success", 
            "message" => "Booking saved successfully",
            "id" => $conn->insert_id
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error inserting record: " . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Incomplete data provided"]);
}

$conn->close();
?>
