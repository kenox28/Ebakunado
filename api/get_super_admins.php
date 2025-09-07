<?php
require_once 'config.php';

// Validate API key
validateApiKey();

try {
    // Get database connection
    $conn = getApiConnection();
    
    // Query to get all super admins (excluding password)
    $query = "SELECT id, super_admin_id, fname, lname, email, created_at, updated_at FROM super_admin ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    if ($result) {
        $super_admins = [];
        while ($row = $result->fetch_assoc()) {
            $super_admins[] = $row;
        }
        
        sendJsonResponse($super_admins);
    } else {
        sendErrorResponse("Failed to fetch super admins", 500);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    sendErrorResponse("Server error occurred", 500);
}
?>