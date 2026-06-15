<?php
// get_bookings.php

require_once 'db_config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

// Get database connection (automatically checks environment and handles connection failure cleanly)
$conn = get_db_connection(true);

// Fetch all bookings ordered by latest signup
$sql = "SELECT `id`, `name`, `mobile`, `city`, `booking_date`, `payment_status`, `created_at` FROM `bookings` ORDER BY `created_at` DESC";
$result = $conn->query($sql);

$bookings = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

echo json_encode(["status" => "success", "bookings" => $bookings]);

$conn->close();
?>
