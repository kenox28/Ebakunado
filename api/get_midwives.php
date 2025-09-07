<?php
require_once 'config.php';

// Validate API key
validateApiKey();

try {
    // Get database connection
    $conn = getApiConnection();
    
    // Query to get all midwives
    $query = "SELECT id, midwife_id, fname, lname, email, profileImg, gender, bdate, license_number, specialization, permissions, Approve, last_active, created_at, updated FROM midwives ORDER BY created_at DESC";
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