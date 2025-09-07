<?php
require_once 'config.php';

// Validate API key
validateApiKey();

try {
    // Get database connection
    $conn = getApiConnection();
    
    // Query to get all activity logs
    $query = "SELECT log_id, user_id, user_type, action_type, description, ip_address, created_at FROM activity_logs ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    if ($result) {
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        
        // Send response in the expected format
        sendJsonResponse([
            'status' => 'success',
            'data' => $logs
        ]);
    } else {
        sendErrorResponse("Failed to fetch activity logs", 500);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    sendErrorResponse("Server error occurred", 500);
}
?>