<?php
require_once 'config.php';

// Validate API key
validateApiKey();

try {
    // Get database connection
    $conn = getApiConnection();
    
    // Query to get all users
    $query = "SELECT * FROM users ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    if ($result) {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        sendJsonResponse($users);
    } else {
        sendErrorResponse("Failed to fetch users", 500);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    sendErrorResponse("Server error occurred", 500);
}
?>