<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$baby_id = $_GET['baby_id'] ?? '';

if (empty($baby_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Baby ID is required']);
    exit();
}

try {
    // Get vaccination records for the specific baby
    $vaccinations = supabaseSelect(
        'immunization_records',
        'status,date_given',
        ['baby_id' => $baby_id]
    );

    $completed = 0;
    $total = 0;

    if ($vaccinations) {
        $total = count($vaccinations);
        foreach ($vaccinations as $vaccination) {
            if ($vaccination['status'] === 'completed' || $vaccination['status'] === 'taken') {
                $completed++;
            }
        }
    }

    $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;

    echo json_encode([
        'status' => 'success',
        'data' => [
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
