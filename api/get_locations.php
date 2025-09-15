<?php
require_once 'config.php';

// Validate API key
validateApiKey();

try {
    // Get database connection
    $conn = getApiConnection();
    
    // Query to get all locations
    $query = "SELECT * FROM locations ORDER BY province, city_municipality, barangay, purok";
    $result = $conn->query($query);
    
    if ($result) {
        $locations = [];
        while ($row = $result->fetch_assoc()) {
            $locations[] = $row;
        }
        
        sendJsonResponse($locations);
    } else {
        sendErrorResponse("Failed to fetch locations", 500);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    sendErrorResponse("Server error occurred", 500);
}
?>
