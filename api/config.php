<?php
// API Database Configuration for InfinityFree/Hostinger
$api_host = "localhost";
$api_username = "root";
$api_password = "";
$api_database = "ebakunado_db";

// API Configuration
$api_key = "iquen"; // Change this to your desired API key

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Set error handler to throw exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Function to connect to API database
function getApiConnection() {
    global $api_host, $api_username, $api_password, $api_database;
    
    try {
        $connection = new mysqli($api_host, $api_username, $api_password, $api_database);
        $connection->set_charset("utf8");
        return $connection;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Database connection failed"
        ]);
        exit();
    }
}

// Function to validate API key
function validateApiKey() {
    global $api_key;
    
    $provided_key = null;
    
    // Check for API key in query parameter
    if (isset($_GET['api_key'])) {
        $provided_key = $_GET['api_key'];
    }
    // Check for API key in header
    elseif (isset($_SERVER['HTTP_X_API_KEY'])) {
        $provided_key = $_SERVER['HTTP_X_API_KEY'];
    }
    
    if ($provided_key !== $api_key) {
        http_response_code(401);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid or missing API key"
        ]);
        exit();
    }
}

// Function to send JSON response
function sendJsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode([
        "status" => "success",
        "data" => $data
    ]);
}

// Function to send error response
function sendErrorResponse($message, $status_code = 400) {
    http_response_code($status_code);
    echo json_encode([
        "status" => "error",
        "message" => $message
    ]);
}
?>