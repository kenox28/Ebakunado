<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

try {
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;
    $offset = ($page - 1) * $limit;

    // Get total count - filter by status='pendingCHR' only (request_type can be 'school' or 'transfer')
    $allRows = supabaseSelect('chrdocrequest', 'id', ['status' => 'pendingCHR'], null);
    $total = $allRows ? count($allRows) : 0;

    // Get paginated requests - filter by status='pendingCHR' only (request_type can be 'school' or 'transfer')
    $requests = supabaseSelect('chrdocrequest', '*', ['status' => 'pendingCHR'], 'created_at.asc', $limit, $offset);
    if (!$requests) {
        $requests = [];
    }
    
    // Enrich with user and child names
    $enrichedData = [];
    if ($requests) {
        foreach ($requests as $req) {
            $userFullname = '';
            $babyName = '';
            
            // Fetch user fullname
            if (!empty($req['user_id'])) {
                $users = supabaseSelect('users', 'fname,lname', ['user_id' => $req['user_id']], null, 1);
                if ($users && count($users) > 0) {
                    $userFullname = trim(($users[0]['fname'] ?? '') . ' ' . ($users[0]['lname'] ?? ''));
                }
            }
            
            // Fetch baby name
            if (!empty($req['baby_id'])) {
                $children = supabaseSelect('child_health_records', 'child_fname,child_lname', ['baby_id' => $req['baby_id']], null, 1);
                if ($children && count($children) > 0) {
                    $babyName = trim(($children[0]['child_fname'] ?? '') . ' ' . ($children[0]['child_lname'] ?? ''));
                }
            }
            
            $enrichedData[] = [
                'id' => $req['id'],
                'user_id' => $req['user_id'],
                'user_fullname' => $userFullname ?: $req['user_id'], // Fallback to user_id if name not found
                'baby_id' => $req['baby_id'],
                'baby_name' => $babyName ?: $req['baby_id'], // Fallback to baby_id if name not found
                'request_type' => $req['request_type'] ?? '',
                'status' => $req['status'] ?? '',
                'created_at' => $req['created_at'] ?? ''
            ];
        }
    }
    
    $has_more = ($offset + $limit) < $total;
    
    echo json_encode([
        'status' => 'success',
        'data' => $enrichedData,
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'has_more' => $has_more
    ]);
} catch (Exception $e) {
    error_log('list_babycard_doc_requests error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to load requests']);
}
?>

