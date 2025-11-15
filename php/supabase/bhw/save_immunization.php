<?php
// Save BHW immunization entry (Supabase)
// Expects: record_id OR (baby_id, vaccine_name, schedule_date)
// Saves: temperature, height, weight, date_given, administered_by, status (completed if mark_completed=1)

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

// Check authorization
if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

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
    
    // Growth assessment data
    $growth_wfa = $_POST['growth_wfa'] ?? '';
    $growth_lfa = $_POST['growth_lfa'] ?? '';
    $growth_wfl = $_POST['growth_wfl'] ?? '';
    $growth_age_months = $_POST['growth_age_months'] ?? '';
    
    // Feeding status updates
    $update_feeding_status = $_POST['update_feeding_status'] ?? '';
    $update_complementary_feeding = $_POST['update_complementary_feeding'] ?? '';
    $update_td_dose_date = $_POST['update_td_dose_date'] ?? '';

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
    
    // Growth assessment classifications
    if ($growth_wfa !== '') { $update['growth_wfa'] = $growth_wfa; }
    if ($growth_lfa !== '') { $update['growth_lfa'] = $growth_lfa; }
    if ($growth_wfl !== '') { $update['growth_wfl'] = $growth_wfl; }
    if ($growth_age_months !== '') { $update['growth_age_months'] = (int)$growth_age_months; }

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

    // Log activity: BHW/Midwife recorded immunization (when mark_completed=1)
    if ($mark_completed) {
        try {
            // Get approver info
            $approver_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
            $approver_type = isset($_SESSION['midwife_id']) ? 'midwife' : 'bhw';
            $approver_name = trim(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? ''));
            
            // Get child info for logging
            $child_info = supabaseSelect('child_health_records', 'child_fname,child_lname,mother_name', ['baby_id' => $record['baby_id']], null, 1);
            $child_name = 'Unknown Child';
            $mother_name = 'Unknown Mother';
            if ($child_info && count($child_info) > 0) {
                $child_name = trim(($child_info[0]['child_fname'] ?? '') . ' ' . ($child_info[0]['child_lname'] ?? ''));
                $mother_name = $child_info[0]['mother_name'] ?? 'Unknown Mother';
            }
            
            $vaccine_name = $record['vaccine_name'] ?? 'Unknown Vaccine';
            $dose_number = $record['dose_number'] ?? 1;
            
            supabaseLogActivity(
                $approver_id,
                $approver_type,
                'IMMUNIZATION_RECORDED',
                $approver_name . ' recorded ' . $vaccine_name . ' (Dose ' . $dose_number . ') for ' . $child_name . ', child of ' . $mother_name . ' (Baby ID: ' . $record['baby_id'] . ', Record ID: ' . $record['id'] . ')',
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        } catch (Exception $e) {
            // Log error but don't fail the update
            error_log('Failed to log immunization recording activity: ' . $e->getMessage());
        }
    }

    // Update feeding status and TD status if provided
    if ($update_feeding_status !== '' || $update_complementary_feeding !== '' || $update_td_dose_date !== '') {
        $feeding_update = [];
        
        // Determine which feeding field to update based on vaccine name and dose
        $vaccine_name = $record['vaccine_name'] ?? '';
        $dose_number = $record['dose_number'] ?? 1;
        
        // Map vaccine to feeding month
        $feeding_month = null;
        if (strpos($vaccine_name, 'BCG') !== false || strpos($vaccine_name, 'HEPAB1') !== false) {
            $feeding_month = 1;
        } else if (strpos($vaccine_name, 'Pentavalent') !== false || strpos($vaccine_name, 'OPV') !== false || strpos($vaccine_name, 'PCV') !== false) {
            $feeding_month = $dose_number + 1; // 1st dose = 2nd month, 2nd dose = 3rd month, etc.
        } else if (strpos($vaccine_name, 'MCV1') !== false) {
            $feeding_month = 6; // MCV1 is around 6th month, show 6th month complementary feeding
        } else if (strpos($vaccine_name, 'MCV2') !== false || strpos($vaccine_name, 'MMR') !== false) {
            $feeding_month = 8; // MCV2/MMR is around 8th month, show 8th month complementary feeding
        }
        
        if ($feeding_month && $feeding_month <= 6 && $update_feeding_status !== '') {
            // Update exclusive breastfeeding
            $field_name = 'exclusive_breastfeeding_' . $feeding_month . 'mo';
            $feeding_update[$field_name] = $update_feeding_status === '1';
        } else if ($feeding_month && $feeding_month >= 6 && $feeding_month <= 8 && $update_complementary_feeding !== '') {
            // Update complementary feeding
            $field_name = 'complementary_feeding_' . $feeding_month . 'mo';
            $feeding_update[$field_name] = $update_complementary_feeding;
        }
        
        // Handle Mother's TD Status update
        if ($update_td_dose_date !== '') {
            // Get current TD status to determine which dose to update
            $current_td = supabaseSelect('child_health_records', 'mother_td_dose1_date,mother_td_dose2_date,mother_td_dose3_date,mother_td_dose4_date,mother_td_dose5_date', ['baby_id' => $record['baby_id']], null, 1);
            
            if ($current_td && count($current_td) > 0) {
                $td_record = $current_td[0];
                $next_dose = 1;
                
                // Find the next dose that needs to be updated
                for ($i = 1; $i <= 5; $i++) {
                    $field_name = 'mother_td_dose' . $i . '_date';
                    if (empty($td_record[$field_name])) {
                        $next_dose = $i;
                        break;
                    }
                }
                
                if ($next_dose <= 5) {
                    $td_field_name = 'mother_td_dose' . $next_dose . '_date';
                    $feeding_update[$td_field_name] = $update_td_dose_date;
                }
            }
        }
        
        if (!empty($feeding_update)) {
            $feeding_update['date_updated'] = date('Y-m-d H:i:s');
            supabaseUpdate('child_health_records', $feeding_update, [ 'baby_id' => $record['baby_id'] ]);
        }
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


