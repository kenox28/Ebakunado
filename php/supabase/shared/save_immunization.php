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
    $remarks = $_POST['remarks'] ?? '';
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
    $previous_batch_schedule = null;
    if ($record_id !== '') {
        $rec = supabaseSelect(
            'immunization_records',
            'id,baby_id,vaccine_name,dose_number,status,schedule_date,batch_schedule_date,date_given,weight,height,temperature,administered_by',
            [ 'id' => $record_id ],
            null,
            1
        );
        if ($rec && is_array($rec) && count($rec) > 0) { 
            $record = $rec[0];
            $previous_batch_schedule = $record['batch_schedule_date'] ?? null;
        }
    } else if ($baby_id !== '' && $vaccine_name !== '' && ($schedule_date !== '' || $catch_up_date !== '')) {
        $lookup_conditions = ($catch_up_date !== ''
            ? [ 'baby_id' => $baby_id, 'vaccine_name' => $vaccine_name, 'catch_up_date' => $catch_up_date ]
            : [ 'baby_id' => $baby_id, 'vaccine_name' => $vaccine_name, 'schedule_date' => $schedule_date ]);
        
        $rec = supabaseSelect(
            'immunization_records',
            'id,baby_id,vaccine_name,dose_number,status,schedule_date,batch_schedule_date,date_given,weight,height,temperature,administered_by',
            $lookup_conditions,
            null,
            1
        );
        if ($rec && is_array($rec) && count($rec) > 0) { 
            $record = $rec[0];
            $previous_batch_schedule = $record['batch_schedule_date'] ?? null;
        }
    }

    if (!$record) {
        echo json_encode([ 'status' => 'error', 'message' => 'Record not found' ]);
        exit();
    }

    $update = [];
    $batch_schedule_date_input = $_POST['batch_schedule_date'] ?? null;
    $batch_schedule_date_provided = array_key_exists('batch_schedule_date', $_POST);

    if ($batch_schedule_date_provided) {
        $normalized = trim((string)$batch_schedule_date_input) === '' ? null : $batch_schedule_date_input;
        $update['batch_schedule_date'] = $normalized;
    }

    if ($temperature !== '') { $update['temperature'] = (float)$temperature; }
    if ($height_cm !== '') { $update['height'] = (float)$height_cm; }
    if ($weight_kg !== '') { $update['weight'] = (float)$weight_kg; }
    if ($administered_by !== '') { $update['administered_by'] = $administered_by; }
    if ($remarks !== '') { $update['remarks'] = $remarks; }
    
    // Growth assessment classifications
    if ($growth_wfa !== '') { $update['growth_wfa'] = $growth_wfa; }
    if ($growth_lfa !== '') { $update['growth_lfa'] = $growth_lfa; }
    if ($growth_wfl !== '') { 
        $update['growth_wfl'] = $growth_wfl;
        // MUAC stores the same classification as weight-for-length/height (obese, normal, etc.)
        $update['muac'] = $growth_wfl;
    }
    if ($growth_age_months !== '') { $update['growth_age_months'] = (int)$growth_age_months; }

    if ($date_taken !== '') {
        $update['date_given'] = $date_taken; // Expecting Y-m-d from client
    }

    if ($mark_completed) {
        // Validate prerequisites before allowing vaccine to be marked as taken
        $vaccine_name = trim($record['vaccine_name'] ?? '');
        $baby_id = $record['baby_id'] ?? '';
        
        // Official vaccine order (from request_immunization.php)
        $vaccine_order = [
            'BCG',
            'Hepatitis B',
            'Pentavalent (DPT-HepB-Hib) - 1st',
            'OPV - 1st',
            'PCV - 1st',
            'Pentavalent (DPT-HepB-Hib) - 2nd',
            'OPV - 2nd',
            'PCV - 2nd',
            'Pentavalent (DPT-HepB-Hib) - 3rd',
            'OPV - 3rd',
            'IPV',
            'PCV - 3rd',
            'MCV1 (AMV)',
            'MCV2 (MMR)'
        ];
        
        // Vaccine name matching function - handles variations
        $matchesVaccine = function($db_name, $order_name) {
            $db_lower = strtolower(trim($db_name));
            $order_lower = strtolower(trim($order_name));
            
            // Exact match
            if ($db_lower === $order_lower) {
                return true;
            }
            
            // Handle Hepatitis B variations
            if ($order_lower === 'hepatitis b') {
                return stripos($db_lower, 'hepatitis') !== false && stripos($db_lower, 'b') !== false ||
                       stripos($db_lower, 'hepab') !== false;
            }
            
            // Handle partial matches for vaccines with dose numbers
            if (stripos($order_lower, 'pentavalent') !== false) {
                return stripos($db_lower, 'pentavalent') !== false && 
                       stripos($db_lower, substr($order_lower, strpos($order_lower, '-'), 10)) !== false;
            }
            if (stripos($order_lower, 'opv') !== false) {
                return stripos($db_lower, 'opv') !== false && 
                       stripos($db_lower, substr($order_lower, strpos($order_lower, '-'), 10)) !== false;
            }
            if (stripos($order_lower, 'pcv') !== false) {
                return stripos($db_lower, 'pcv') !== false && 
                       stripos($db_lower, substr($order_lower, strpos($order_lower, '-'), 10)) !== false;
            }
            if (stripos($order_lower, 'mcv1') !== false || stripos($order_lower, '(amv)') !== false) {
                return stripos($db_lower, 'mcv1') !== false || stripos($db_lower, '(amv)') !== false;
            }
            if (stripos($order_lower, 'mcv2') !== false || stripos($order_lower, '(mmr)') !== false) {
                return stripos($db_lower, 'mcv2') !== false || stripos($db_lower, '(mmr)') !== false;
            }
            
            // BCG and IPV exact or partial
            if ($order_lower === 'bcg') {
                return $db_lower === 'bcg';
            }
            if ($order_lower === 'ipv') {
                return $db_lower === 'ipv';
            }
            
            return false;
        };
        
        // Find current vaccine position in order
        $current_index = -1;
        foreach ($vaccine_order as $index => $ordered_vaccine) {
            if ($matchesVaccine($vaccine_name, $ordered_vaccine)) {
                $current_index = $index;
                break;
            }
        }
        
        // Only validate vaccines that are in our official order list
        if ($current_index >= 0 && $current_index > 0) {
            $record_id = $record['id'] ?? '';
            
            // Get ALL immunization records for this baby
            $all_records = supabaseSelect(
                'immunization_records',
                'id,vaccine_name,status',
                ['baby_id' => $baby_id],
                null,
                null
            );
            
            if (!$all_records || !is_array($all_records)) {
                $all_records = [];
            }
            
            // Build map of vaccine statuses (index => status)
            $vaccine_status_map = [];
            foreach ($all_records as $rec) {
                $rec_vaccine_name = trim($rec['vaccine_name'] ?? '');
                $rec_status = strtolower(trim($rec['status'] ?? ''));
                $rec_id = $rec['id'] ?? null;
                
                // Skip current record being updated
                if ($rec_id == $record_id) {
                    continue;
                }
                
                if (!empty($rec_vaccine_name)) {
                    // For each vaccine in order, check what status it has
                    foreach ($vaccine_order as $idx => $ordered_vaccine) {
                        if ($matchesVaccine($rec_vaccine_name, $ordered_vaccine)) {
                            // If we found this vaccine before, keep the most "taken" status
                            if (!isset($vaccine_status_map[$idx])) {
                                $vaccine_status_map[$idx] = $rec_status;
                            } else {
                                // Prefer 'taken' or 'completed' over other statuses
                                if ($rec_status === 'taken' || $rec_status === 'completed') {
                                    $vaccine_status_map[$idx] = $rec_status;
                                }
                            }
                            break;
                        }
                    }
                }
            }
            
            // Check ALL prerequisites (all vaccines before current index)
            $missing_or_not_taken = [];
            $missed_vaccines = [];
            
            for ($i = 0; $i < $current_index; $i++) {
                $prereq_vaccine = $vaccine_order[$i];
                $prereq_status = $vaccine_status_map[$i] ?? 'missing';
                
                // Check if prerequisite exists and is taken/completed
                if ($prereq_status === 'missing' || 
                    ($prereq_status !== 'taken' && $prereq_status !== 'completed')) {
                    
                    // Check if it's missed or scheduled
                    if ($prereq_status === 'missed') {
                        $missed_vaccines[] = $prereq_vaccine;
                    } else {
                        $missing_or_not_taken[] = $prereq_vaccine . ' (Status: ' . ($prereq_status === 'missing' ? 'Not recorded' : ucfirst($prereq_status)) . ')';
                    }
                }
            }
            
            // Block save if ANY prerequisite is missing, not taken, missed, or scheduled
            if (!empty($missed_vaccines) || !empty($missing_or_not_taken)) {
                $error_parts = [];
                
                if (!empty($missed_vaccines)) {
                    $error_parts[] = 'Missed vaccines that must be caught up first: ' . implode(', ', $missed_vaccines);
                }
                
                if (!empty($missing_or_not_taken)) {
                    $error_parts[] = 'Missing or incomplete prerequisites: ' . implode(', ', $missing_or_not_taken);
                }
                
                $error_message = 'Cannot record ' . htmlspecialchars($vaccine_name) . '. ';
                $error_message .= implode('. ', $error_parts);
                $error_message .= '. Please complete all previous vaccines before recording this one.';
                
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => $error_message
                ]);
                exit();
            }
        }
        
        // Business rule: mark as 'taken' when BHW records immunization
        $update['status'] = 'taken';
        if ($date_taken === '' && !isset($update['date_given'])) {
            $update['date_given'] = date('Y-m-d');
        }
    }

    // Always update updated timestamp if column exists (named 'updated' in schema)
    // Only add if we have other fields to update (to avoid unnecessary updates)
    if (count($update) > 0) {
        $update['updated'] = date('Y-m-d H:i:s');
    }

    if (count($update) === 0) {
        echo json_encode([ 'status' => 'error', 'message' => 'No fields to update' ]);
        exit();
    }

    $res = supabaseUpdate('immunization_records', $update, [ 'id' => $record['id'] ]);

    if ($res === false) {
        $error = getSupabase()->getLastError() ?? 'Unknown error';
        echo json_encode([ 'status' => 'error', 'message' => 'Update failed: ' . $error ]);
        exit();
    }
    
    // Empty array result means no rows changed, but this can be OK if values are already set
    // Verify the record still exists and update was processed
    if (is_array($res) && count($res) === 0) {
        $verify = supabaseSelect('immunization_records', 'id,status,date_given', [ 'id' => $record['id'] ], null, 1);
        if (!$verify || count($verify) === 0) {
            echo json_encode([ 'status' => 'error', 'message' => 'Record not found after update attempt' ]);
            exit();
        }
        // If we were updating status but it didn't change, that's OK - continue
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
        
        // Handle Mother's TD Status update (via mother_tetanus_doses)
        if ($update_td_dose_date !== '') {
            // Use string users.user_id directly (matches schema)
            $child_row = supabaseSelect('child_health_records', 'user_id', ['baby_id' => $record['baby_id']], null, 1);
            $string_user_id = ($child_row && count($child_row) > 0) ? ($child_row[0]['user_id'] ?? '') : '';

            if ($string_user_id !== '') {
                $tdRows = supabaseSelect('mother_tetanus_doses', 'id,dose1_date,dose2_date,dose3_date,dose4_date,dose5_date', ['user_id' => $string_user_id], null, 1);
                if ($tdRows && count($tdRows) > 0) {
                    $td = $tdRows[0];
                    $next = null;
                    for ($i=1; $i<=5; $i++) {
                        $k = 'dose'.$i.'_date';
                        if (empty($td[$k])) { $next = $k; break; }
                    }
                    if ($next) {
                        supabaseUpdate('mother_tetanus_doses', [ $next => $update_td_dose_date, 'date_updated' => date('Y-m-d H:i:s') ], ['id' => $td['id']]);
                    }
                } else {
                    supabaseInsert('mother_tetanus_doses', [
                        'user_id' => $string_user_id,
                        'dose1_date' => $update_td_dose_date,
                        'date_created' => date('Y-m-d H:i:s'),
                        'date_updated' => date('Y-m-d H:i:s')
                    ]);
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


