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
    
    // OPTIMIZATION: Fetch ALL user and child names in batch queries (fixes N+1 problem)
    $enrichedData = [];
    if ($requests) {
        // Get all unique user_ids and baby_ids
        $userIds = array_filter(array_unique(array_column($requests, 'user_id')));
        $babyIds = array_filter(array_unique(array_column($requests, 'baby_id')));
        
        // Fetch ALL users in ONE query
        $usersMap = [];
        if (!empty($userIds)) {
            $users = supabaseSelect('users', 'user_id,fname,lname', ['user_id' => $userIds]);
            if ($users) {
                foreach ($users as $u) {
                    $uid = $u['user_id'] ?? '';
                    if ($uid !== '') {
                        $usersMap[$uid] = trim(($u['fname'] ?? '') . ' ' . ($u['lname'] ?? ''));
                    }
                }
            }
        }
        
        // Fetch ALL children in ONE query
        $childrenMap = [];
        if (!empty($babyIds)) {
            $children = supabaseSelect('child_health_records', 'baby_id,child_fname,child_lname', ['baby_id' => $babyIds]);
            if ($children) {
                foreach ($children as $c) {
                    $bid = $c['baby_id'] ?? '';
                    if ($bid !== '') {
                        $childrenMap[$bid] = trim(($c['child_fname'] ?? '') . ' ' . ($c['child_lname'] ?? ''));
                    }
                }
            }
        }
        
        // Enrich requests using the maps (no queries in loop!)
        foreach ($requests as $req) {
            $userFullname = $usersMap[$req['user_id']] ?? '';
            $babyName = $childrenMap[$req['baby_id']] ?? '';
            
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

