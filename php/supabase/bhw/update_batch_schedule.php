<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$payload = json_decode(file_get_contents('php://input'), true);
$recordIds = $payload['record_ids'] ?? [];
$batchDate = $payload['batch_schedule_date'] ?? null;

if (!is_array($recordIds) || empty($recordIds)) {
    echo json_encode(['status' => 'error', 'message' => 'No records provided']);
    exit();
}

if ($batchDate !== null && trim($batchDate) === '') {
    $batchDate = null;
}

$actorId = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'];
$actorType = isset($_SESSION['midwife_id']) ? 'midwife' : 'bhw';
$actorName = trim(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? ''));

$updated = 0;
$errors = [];

foreach ($recordIds as $recordId) {
    if (!$recordId) {
        continue;
    }

    $updateResult = supabaseUpdate('immunization_records', ['batch_schedule_date' => $batchDate], ['id' => $recordId]);

    if ($updateResult === false) {
        $errors[] = $recordId;
        continue;
    }

    $updated++;
}

if ($updated > 0) {
    supabaseLogActivity(
        $actorId,
        $actorType,
        'BATCH_SCHEDULE_UPDATE',
        sprintf(
            '%s updated batch schedule date to %s for %d record(s)',
            $actorName ?: ucfirst($actorType),
            $batchDate ?: 'none',
            $updated
        ),
        $_SERVER['REMOTE_ADDR'] ?? null
    );
}

echo json_encode([
    'status' => 'success',
    'data' => [
        'updated' => $updated,
        'failed' => $errors
    ]
]);

