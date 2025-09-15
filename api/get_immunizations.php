<?php
require_once 'config.php';

// Validate API key
validateApiKey();

try {
    // Get database connection
    $conn = getApiConnection();
    
    // Query to get all immunization records
    $query = "SELECT * FROM immunization_records ORDER BY date_given DESC";
    $result = $conn->query($query);
    
    if ($result) {
        $immunizations = [];
        while ($row = $result->fetch_assoc()) {
            $immunizations[] = $row;
        }
        
        sendJsonResponse($immunizations);
    } else {
        sendErrorResponse("Failed to fetch immunization records", 500);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    sendErrorResponse("Server error occurred", 500);
}
?>
