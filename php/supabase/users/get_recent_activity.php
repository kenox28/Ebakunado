<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $activities = [];

    // Get recent approved CHR document requests
    $chr_requests = supabaseSelect(
        'chrdocrequest',
        'baby_id,request_type,status,created_at,doc_url',
        ['user_id' => $user_id, 'status' => 'approved'],
        'created_at.desc',
        10
    );

    if ($chr_requests) {
        // Get child names for the CHR requests
        $baby_ids = array_column($chr_requests, 'baby_id');
        $children = [];
        
        if (!empty($baby_ids)) {
            $child_records = supabaseSelect(
                'child_health_records',
                'baby_id,child_fname,child_lname',
                ['user_id' => $user_id, 'baby_id' => $baby_ids]
            );
            
            foreach ($child_records as $child) {
                $children[$child['baby_id']] = $child;
            }
        }

        foreach ($chr_requests as $request) {
            $child = $children[$request['baby_id']] ?? null;
            $childName = $child ? "{$child['child_fname']} {$child['child_lname']}" : "Unknown Child";
            $requestType = ucfirst($request['request_type']);
            
            $activities[] = [
                'type' => 'approval',
                'title' => 'CHR Document Approved',
                'description' => "{$requestType} copy approved for {$childName}",
                'timestamp' => $request['created_at']
            ];
        }
    }

    // Only show approved CHR documents - vaccination completions removed as requested

    // Sort activities by timestamp (most recent first)
    usort($activities, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    // Limit to 10 most recent activities
    $activities = array_slice($activities, 0, 10);

    echo json_encode([
        'status' => 'success',
        'data' => $activities
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
