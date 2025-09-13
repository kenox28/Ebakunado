
<?php
session_start();
include "../../database/Database.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to browser, but log them

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
    error_log("Edit user: Unauthorized access attempt");
    echo json_encode(array('status' => 'error', 'message' => 'Unauthorized access'));
    exit();
}

// Check database connection
if (!$connect) {
    error_log("Edit user: Database connection failed");
    echo json_encode(array('status' => 'error', 'message' => 'Database connection failed'));
    exit();
}

// Check if request method is POST
// Handle both GET (for fetching) and POST (for updating)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_GET['user_id'] ?? '';
} else {
    $user_id = $_POST['user_id'] ?? '';
}

error_log("Edit user: Attempting to edit user_id: '$user_id'");

if(empty($user_id)) {
    error_log("Edit user: Empty user_id provided");
    echo json_encode(array('status' => 'error', 'message' => 'User ID is required'));
    exit();
}

try {
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $connect->prepare($sql);
    
    if (!$stmt) {
        error_log("Edit user: Failed to prepare statement - " . $connect->error);
        echo json_encode(array('status' => 'error', 'message' => 'Database prepare error'));
        exit();
    }
    
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    error_log("Edit user: Query executed, rows found: " . $result->num_rows);
    
    if ($result->num_rows === 0) {
        error_log("Edit user: No user found with ID: '$user_id'");
        echo json_encode(array('status' => 'error', 'message' => 'User not found'));
        exit();
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    error_log("Edit user: Successfully retrieved user data for: " . $user['fname'] . ' ' . $user['lname']);
    echo json_encode(array('status' => 'success', 'data' => $user));
    
} catch (Exception $e) {
    error_log("Edit user: Exception occurred - " . $e->getMessage());
    echo json_encode(array('status' => 'error', 'message' => 'Database error: ' . $e->getMessage()));
}
?>


