<?php
session_start();
include_once "../../../database/Database.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

try {
    if (!$connect) {
        throw new Exception("Database connection failed");
    }

    $location_id = intval($_POST['location_id'] ?? 0);

    if ($location_id <= 0) {
        throw new Exception("Invalid location ID");
    }

    // Get location details for logging
    $get_query = "SELECT province, city_municipality, barangay, purok FROM locations WHERE id = ?";
    $get_stmt = $connect->prepare($get_query);
    $get_stmt->bind_param("i", $location_id);
    $get_stmt->execute();
    $location_result = $get_stmt->get_result();
    
    if ($location_result->num_rows === 0) {
        throw new Exception("Location not found");
    }
    
    $location_data = $location_result->fetch_assoc();

    // Delete location
    $delete_query = "DELETE FROM locations WHERE id = ?";
    $delete_stmt = $connect->prepare($delete_query);
    $delete_stmt->bind_param("i", $location_id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception("Failed to delete location");
    }

    // Log the activity
    $user_id = $_SESSION['admin_id'] ?? $_SESSION['super_admin_id'];
    $user_type = isset($_SESSION['admin_id']) ? 'admin' : 'super_admin';
    $action_type = 'DELETE_LOCATION';
    $description = "Deleted location: {$location_data['province']}, {$location_data['city_municipality']}, {$location_data['barangay']}, {$location_data['purok']}";
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

    $log_query = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address) VALUES (?, ?, ?, ?, ?)";
    $log_stmt = $connect->prepare($log_query);
    $log_stmt->bind_param("sssss", $user_id, $user_type, $action_type, $description, $ip_address);
    $log_stmt->execute();

    echo json_encode(["status" => "success", "message" => "Location deleted successfully"]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
