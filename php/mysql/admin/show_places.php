<?php
session_start();
include_once "../../../database/Database.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

try {
    if (!$connect) {
        throw new Exception("Database connection failed");
    }

    $query = "SELECT id as location_id, province, city_municipality, barangay, purok, created_at FROM locations ORDER BY province, city_municipality, barangay, purok";
    $result = mysqli_query($connect, $query);
    
    if (!$result) {
        throw new Exception("Failed to fetch locations: " . mysqli_error($connect));
    }
    
    $locations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $locations[] = $row;
    }
    
    echo json_encode($locations);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
