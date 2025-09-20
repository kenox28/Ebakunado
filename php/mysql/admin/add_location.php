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

    $province = trim($_POST['province'] ?? '');
    $city_municipality = trim($_POST['city_municipality'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $purok = trim($_POST['purok'] ?? '');

    if (empty($province) || empty($city_municipality) || empty($barangay) || empty($purok)) {
        throw new Exception("All location fields are required");
    }

    // Check if location already exists
    $check_query = "SELECT id FROM locations WHERE province = ? AND city_municipality = ? AND barangay = ? AND purok = ?";
    $check_stmt = $connect->prepare($check_query);
    $check_stmt->bind_param("ssss", $province, $city_municipality, $barangay, $purok);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        throw new Exception("This location already exists");
    }

    // Insert new location
    $insert_query = "INSERT INTO locations (province, city_municipality, barangay, purok) VALUES (?, ?, ?, ?)";
    $insert_stmt = $connect->prepare($insert_query);
    $insert_stmt->bind_param("ssss", $province, $city_municipality, $barangay, $purok);
    
    if (!$insert_stmt->execute()) {
        throw new Exception("Failed to add location");
    }

    // Log the activity
    $user_id = $_SESSION['admin_id'] ?? $_SESSION['super_admin_id'];
    $user_type = isset($_SESSION['admin_id']) ? 'admin' : 'super_admin';
    $action_type = 'ADD_LOCATION';
    $description = "Added new location: $province, $city_municipality, $barangay, $purok";
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

    $log_query = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address) VALUES (?, ?, ?, ?, ?)";
    $log_stmt = $connect->prepare($log_query);
    $log_stmt->bind_param("sssss", $user_id, $user_type, $action_type, $description, $ip_address);
    $log_stmt->execute();

    echo json_encode(["status" => "success", "message" => "Location added successfully"]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
