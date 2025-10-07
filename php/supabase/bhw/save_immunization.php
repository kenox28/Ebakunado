<?php
// Save BHW immunization entry (Supabase)
// Expects: record_id OR (baby_id, vaccine_name, schedule_date)
// Saves: temperature, height, weight, date_given, administered_by, status (completed if mark_completed=1)

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

try {
    // Read inputs
    $record_id = $_POST['record_id'] ?? '';
    $baby_id = $_POST['baby_id'] ?? '';
    $vaccine_name = $_POST['vaccine_name'] ?? '';
    $schedule_date = $_POST['schedule_date'] ?? '';
    $catch_up_date = $_POST['catch_up_date'] ?? '';

    $date_taken = $_POST['date_taken'] ?? '';
    $temperature = $_POST['temperature'] ?? '';
    $height_cm = $_POST['height_cm'] ?? '';
    $weight_kg = $_POST['weight_kg'] ?? '';
    $administered_by = $_POST['administered_by'] ?? '';
    $mark_completed = isset($_POST['mark_completed']) ? ($_POST['mark_completed'] === '1') : false;

    // Validate identifiers
    $record = null;
    if ($record_id !== '') {
        $rec = supabaseSelect('immunization_records', 'id,baby_id,vaccine_name,dose_number,status,schedule_date,date_given,weight,height,temperature,administered_by', [ 'id' => $record_id ], null, 1);
        if ($rec && is_array($rec) && count($rec) > 0) { $record = $rec[0]; }
    } else if ($baby_id !== '' && $vaccine_name !== '' && ($schedule_date !== '' || $catch_up_date !== '')) {
        $rec = supabaseSelect(
            'immunization_records',
            'id,baby_id,vaccine_name,dose_number,status,schedule_date,date_given,weight,height,temperature,administered_by',
            ($catch_up_date !== ''
                ? [ 'baby_id' => $baby_id, 'vaccine_name' => $vaccine_name, 'catch_up_date' => $catch_up_date ]
                : [ 'baby_id' => $baby_id, 'vaccine_name' => $vaccine_name, 'schedule_date' => $schedule_date ]),
            null,
            1
        );
        if ($rec && is_array($rec) && count($rec) > 0) { $record = $rec[0]; }
    }

    if (!$record) {
        echo json_encode([ 'status' => 'error', 'message' => 'Record not found' ]);
        exit();
    }

    $update = [];

    if ($temperature !== '') { $update['temperature'] = (float)$temperature; }
    if ($height_cm !== '') { $update['height'] = (float)$height_cm; }
    if ($weight_kg !== '') { $update['weight'] = (float)$weight_kg; }
    if ($administered_by !== '') { $update['administered_by'] = $administered_by; }

    if ($date_taken !== '') {
        $update['date_given'] = $date_taken; // Expecting Y-m-d from client
    }

    if ($mark_completed) {
        // Business rule: mark as 'taken' when BHW records immunization
        $update['status'] = 'taken';
        if ($date_taken === '' && !isset($update['date_given'])) {
            $update['date_given'] = date('Y-m-d');
        }
    }

    // Always update updated timestamp if column exists (named 'updated' in schema)
    $update['updated'] = date('Y-m-d H:i:s');

    if (count($update) === 0) {
        echo json_encode([ 'status' => 'error', 'message' => 'No fields to update' ]);
        exit();
    }

    $res = supabaseUpdate('immunization_records', $update, [ 'id' => $record['id'] ]);

    if ($res === false || (is_array($res) && count($res) === 0)) {
        echo json_encode([ 'status' => 'error', 'message' => 'Update failed or no rows affected' ]);
        exit();
    }

    // Return the updated record
    $updated = supabaseSelect('immunization_records', '*', [ 'id' => $record['id'] ], null, 1);
    $updated_rec = ($updated && is_array($updated) && count($updated) > 0) ? $updated[0] : null;

    echo json_encode([
        'status' => 'success',
        'message' => 'Immunization updated',
        'record' => $updated_rec
    ]);
    exit();

} catch (Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
    exit();
}

?>


