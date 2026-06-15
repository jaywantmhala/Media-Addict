<?php
// delete_booking.php

require_once 'db_config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get database connection (automatically checks environment and handles connection failure cleanly)
$conn = get_db_connection(true);

// Get POST data
$data = json_decode(file_get_contents("php://input"));

if (isset($data->id)) {
    $id = intval($data->id);
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM `bookings` WHERE `id` = ?");
    
    if (!$stmt) {
        die(json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]));
    }
    
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Booking deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Booking not found or already deleted"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Error executing query: " . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Incomplete request. No ID provided."]);
}

$conn->close();
?>
