<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['super_admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

try {
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, (int) $_GET['limit'])) : 10;
    $search = strtolower(trim($_GET['search'] ?? ''));

    $users = supabaseSelect('users', '*', [], 'created_at.desc') ?: [];

    if ($search !== '') {
        $users = array_values(array_filter($users, function ($user) use ($search) {
            $columns = [
                'user_id',
                'fname',
                'lname',
                'email',
                'phone_number',
                'gender',
                'place',
                'role'
            ];
            foreach ($columns as $col) {
                if (strpos(strtolower((string) ($user[$col] ?? '')), $search) !== false) {
                    return true;
                }
            }
            return false;
        }));
    }

    $total = count($users);
    $totalPages = $total > 0 ? ceil($total / $limit) : 1;

    if ($page > $totalPages) {
        $page = $totalPages;
    }

    $start = ($page - 1) * $limit;
    $data = array_slice($users, $start, $limit);

    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'has_more' => ($start + count($data)) < $total
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load users: ' . $e->getMessage()
    ]);
}

