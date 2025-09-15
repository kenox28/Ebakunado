<?php
require_once 'config.php';

// Validate API key
validateApiKey();

try {
    // Get database connection
    $conn = getApiConnection();
    
    // Query to get all midwives
        $query = "SELECT * FROM midwives ORDER BY created_at DESC";
        $result = $conn->query($query);
    
    if ($result) {
        $midwives = [];
        while ($row = $result->fetch_assoc()) {
            $midwives[] = $row;
        }
        
        sendJsonResponse($midwives);
    } else {
        sendErrorResponse("Failed to fetch midwives", 500);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    sendErrorResponse("Server error occurred", 500);
}
?>