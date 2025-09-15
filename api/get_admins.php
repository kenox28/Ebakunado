<?php
require_once 'config.php';

// Validate API key
validateApiKey();

try {
    // Get database connection
    $conn = getApiConnection();
    
    // Query to get all admins (excluding password)
    $query = "SELECT * FROM admin ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    if ($result) {
        $admins = [];
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
        
        sendJsonResponse($admins);
    } else {
        sendErrorResponse("Failed to fetch admins", 500);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    sendErrorResponse("Server error occurred", 500);
}
?>
