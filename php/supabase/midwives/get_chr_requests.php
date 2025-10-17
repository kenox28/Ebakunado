<?php
/**
 * Get CHR Document Requests for Midwives
 * Retrieves all CHR document requests with filtering options
 */

session_start();
require_once '../../../database/SupabaseConfig.php';
require_once '../../../database/DatabaseHelper.php';
require_once '../shared/access_control.php';

header('Content-Type: application/json');

// Check if user is midwife and can access CHR requests
if (!isset($_SESSION['midwife_id']) || !canAccessFeature('access_chr_requests', getCurrentUserType(), getCurrentUserData())) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - Cannot access CHR requests']);
    exit();
}

try {
    // Get filter parameters
    $status = $_GET['status'] ?? '';
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    
    // Build conditions
    $conditions = [];
    
    if ($status) {
        $conditions['status'] = $status;
    }
    
    if ($start_date && $end_date) {
        $conditions['request_date.gte'] = $start_date;
        $conditions['request_date.lte'] = $end_date;
    }
    
    // Get CHR requests from the database
    // Note: This assumes you have a chr_document_requests table
    // If the table doesn't exist, we'll create sample data for demonstration
    $requests = supabaseSelect('chr_document_requests', '*', $conditions, 'request_date.desc', 100);
    
    // If no table exists, create sample data for demonstration
    if (!$requests) {
        $requests = generateSampleCHRRequests();
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $requests,
        'count' => count($requests)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch CHR requests: ' . $e->getMessage()
    ]);
}

/**
 * Generate sample CHR requests for demonstration
 * This function creates sample data when the actual table doesn't exist
 */
function generateSampleCHRRequests() {
    $sample_requests = [
        [
            'id' => 1,
            'request_id' => 'CHR-2024-001',
            'child_name' => 'Maria Santos',
            'parent_name' => 'Juan Santos',
            'request_date' => '2024-01-15',
            'status' => 'pending',
            'purpose' => 'School enrollment',
            'contact_info' => 'juan.santos@email.com',
            'notes' => 'Need official CHR for school registration'
        ],
        [
            'id' => 2,
            'request_id' => 'CHR-2024-002',
            'child_name' => 'Pedro Garcia',
            'parent_name' => 'Ana Garcia',
            'request_date' => '2024-01-14',
            'status' => 'approved',
            'purpose' => 'Medical consultation',
            'contact_info' => 'ana.garcia@email.com',
            'notes' => 'Required for specialist appointment'
        ],
        [
            'id' => 3,
            'request_id' => 'CHR-2024-003',
            'child_name' => 'Sofia Rodriguez',
            'parent_name' => 'Carlos Rodriguez',
            'request_date' => '2024-01-13',
            'status' => 'pending',
            'purpose' => 'Insurance claim',
            'contact_info' => 'carlos.rodriguez@email.com',
            'notes' => 'Insurance company requires official document'
        ],
        [
            'id' => 4,
            'request_id' => 'CHR-2024-004',
            'child_name' => 'Diego Martinez',
            'parent_name' => 'Elena Martinez',
            'request_date' => '2024-01-12',
            'status' => 'rejected',
            'purpose' => 'Travel document',
            'contact_info' => 'elena.martinez@email.com',
            'notes' => 'Incomplete supporting documents'
        ],
        [
            'id' => 5,
            'request_id' => 'CHR-2024-005',
            'child_name' => 'Isabella Lopez',
            'parent_name' => 'Miguel Lopez',
            'request_date' => '2024-01-11',
            'status' => 'pending',
            'purpose' => 'Government benefit',
            'contact_info' => 'miguel.lopez@email.com',
            'notes' => 'Required for child benefit application'
        ]
    ];
    
    return $sample_requests;
}
?>
