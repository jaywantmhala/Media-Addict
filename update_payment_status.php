<?php
// update_payment_status.php

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

// Get database connection
$conn = get_db_connection(true);

// Get POST data
$data = json_decode(file_get_contents("php://input"));

if (isset($data->id) && isset($data->status)) {
    $id = intval($data->id);
    $status = $conn->real_escape_string($data->status);
    
    // Validate status values
    $allowed_statuses = ['Pending', 'Completed', 'Failed'];
    if (!in_array($status, $allowed_statuses)) {
        die(json_encode(["status" => "error", "message" => "Invalid status value provided."]));
    }
    
    // Prepare statement to update status
    $stmt = $conn->prepare("UPDATE `bookings` SET `payment_status` = ? WHERE `id` = ?");
    
    if (!$stmt) {
        die(json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]));
    }
    
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Payment status updated to $status."]);
        } else {
            // It could be that the status was already set to $status
            echo json_encode(["status" => "success", "message" => "No rows changed (status might already be $status)."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Error executing update query: " . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Incomplete request. ID and Status are required."]);
}

$conn->close();
?>
