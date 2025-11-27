<?php
include "../../../database/SupabaseConfig.php";
include "../../../database/DatabaseHelper.php";

header('Content-Type: application/json');

try {
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, (int) $_GET['limit'])) : 10;
    $search = strtolower(trim($_GET['search'] ?? ''));
    $startDate = $_GET['start'] ?? '';
    $endDate = $_GET['end'] ?? '';

    $consents = supabaseSelect('user_privacy_consents', '*', [], 'agreed_date.desc') ?: [];

    if ($search !== '') {
        $consents = array_values(array_filter($consents, function ($row) use ($search) {
            $columns = ['full_name', 'email', 'phone_number', 'ip_address'];
            foreach ($columns as $col) {
                if (strpos(strtolower((string) ($row[$col] ?? '')), $search) !== false) {
                    return true;
                }
            }
            return false;
        }));
    }

    if ($startDate !== '') {
        $start = strtotime($startDate . ' 00:00:00');
        $consents = array_values(array_filter($consents, function ($row) use ($start) {
            $date = strtotime($row['agreed_date'] ?? '');
            return $date && $date >= $start;
        }));
    }

    if ($endDate !== '') {
        $end = strtotime($endDate . ' 23:59:59');
        $consents = array_values(array_filter($consents, function ($row) use ($end) {
            $date = strtotime($row['agreed_date'] ?? '');
            return $date && $date <= $end;
        }));
    }

    $total = count($consents);
    $totalPages = $total > 0 ? ceil($total / $limit) : 1;
    if ($page > $totalPages) {
        $page = $totalPages;
    }

    $start = ($page - 1) * $limit;
    $data = array_slice($consents, $start, $limit);

    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'has_more' => ($start + count($data)) < $total
    ]);
} catch (Exception $e) {
    error_log('Failed to load privacy consents: ' . $e->getMessage());
    echo json_encode([
        'status' => 'failed',
        'message' => 'An unexpected error occurred while loading consents.'
    ]);
}

