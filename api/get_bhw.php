<?php
require_once 'config.php';

// Validate API key
validateApiKey();

try {
    // Get database connection
    $conn = getApiConnection();
    
    // Query to get all BHW records
    $query = "SELECT id, bhw_id, fname, lname, email, profileImg, gender, bdate, barangay, permissions, last_active, created_at, updated FROM bhw ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    if ($result) {
        $bhw_records = [];
        while ($row = $result->fetch_assoc()) {
            $bhw_records[] = $row;
        }
        
        sendJsonResponse($bhw_records);
    } else {
        sendErrorResponse("Failed to fetch BHW records", 500);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    sendErrorResponse("Server error occurred", 500);
}
?>