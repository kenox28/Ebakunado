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
    $batch_schedule_changed = false;

    if ($batch_schedule_date_provided) {
        $normalized = trim((string)$batch_schedule_date_input) === '' ? null : $batch_schedule_date_input;
        $update['batch_schedule_date'] = $normalized;
        $batch_schedule_changed = $normalized !== $previous_batch_schedule;
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
        // VALIDATION: Check prerequisites before allowing vaccine to be marked as taken
        // All previous vaccines in sequence must be TAKEN/COMPLETED before proceeding
        $vaccine_name = trim($record['vaccine_name'] ?? '');
        $baby_id = $record['baby_id'] ?? '';
        $record_id = $record['id'] ?? '';
        $current_status = strtolower(trim($record['status'] ?? ''));
        
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
                return (stripos($db_lower, 'hepatitis') !== false && stripos($db_lower, 'b') !== false) ||
                       stripos($db_lower, 'hepab') !== false;
            }
            
            // Handle Pentavalent with dose numbers
            if (stripos($order_lower, 'pentavalent') !== false) {
                if (stripos($db_lower, 'pentavalent') === false) return false;
                if (stripos($order_lower, '1st') !== false && stripos($db_lower, '1st') !== false) return true;
                if (stripos($order_lower, '2nd') !== false && stripos($db_lower, '2nd') !== false) return true;
                if (stripos($order_lower, '3rd') !== false && stripos($db_lower, '3rd') !== false) return true;
                return false;
            }
            
            // Handle OPV with dose numbers
            if (stripos($order_lower, 'opv') !== false) {
                if (stripos($db_lower, 'opv') === false) return false;
                if (stripos($order_lower, '1st') !== false && stripos($db_lower, '1st') !== false) return true;
                if (stripos($order_lower, '2nd') !== false && stripos($db_lower, '2nd') !== false) return true;
                if (stripos($order_lower, '3rd') !== false && stripos($db_lower, '3rd') !== false) return true;
                return false;
            }
            
            // Handle PCV with dose numbers
            if (stripos($order_lower, 'pcv') !== false) {
                if (stripos($db_lower, 'pcv') === false) return false;
                if (stripos($order_lower, '1st') !== false && stripos($db_lower, '1st') !== false) return true;
                if (stripos($order_lower, '2nd') !== false && stripos($db_lower, '2nd') !== false) return true;
                if (stripos($order_lower, '3rd') !== false && stripos($db_lower, '3rd') !== false) return true;
                return false;
            }
            
            // Handle MCV1
            if (stripos($order_lower, 'mcv1') !== false || stripos($order_lower, '(amv)') !== false) {
                return stripos($db_lower, 'mcv1') !== false || stripos($db_lower, '(amv)') !== false;
            }
            
            // Handle MCV2
            if (stripos($order_lower, 'mcv2') !== false || stripos($order_lower, '(mmr)') !== false) {
                return stripos($db_lower, 'mcv2') !== false || stripos($db_lower, '(mmr)') !== false;
            }
            
            // BCG and IPV exact match
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
        // If vaccine not in order list, allow it (might be custom/other vaccine)
        if ($current_index >= 0 && $current_index > 0) {
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
            
            // Build map of vaccine statuses (vaccine_name => status)
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
        
        // All prerequisites met - allow marking as taken
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

    // Activity logs
    $actor_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
    $actor_type = isset($_SESSION['midwife_id']) ? 'midwife' : 'bhw';
    $actor_name = trim(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? ''));
    $child_info = null;
    $child_name = 'Unknown Child';
    $mother_name = 'Unknown Mother';
    $vaccine_name = $record['vaccine_name'] ?? 'Unknown Vaccine';
    $dose_number = $record['dose_number'] ?? 1;

    try {
        $child_info = supabaseSelect('child_health_records', 'child_fname,child_lname,mother_name', ['baby_id' => $record['baby_id']], null, 1);
        if ($child_info && count($child_info) > 0) {
            $child_name = trim(($child_info[0]['child_fname'] ?? '') . ' ' . ($child_info[0]['child_lname'] ?? ''));
            $mother_name = $child_info[0]['mother_name'] ?? 'Unknown Mother';
        }
    } catch (Exception $e) {
        error_log('Failed to fetch child info for activity log: ' . $e->getMessage());
    }

    if ($mark_completed) {
        try {
            supabaseLogActivity(
                $actor_id,
                $actor_type,
                'IMMUNIZATION_RECORDED',
                $actor_name . ' recorded ' . $vaccine_name . ' (Dose ' . $dose_number . ') for ' . $child_name . ', child of ' . $mother_name . ' (Baby ID: ' . $record['baby_id'] . ', Record ID: ' . $record['id'] . ')',
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        } catch (Exception $e) {
            // Log error but don't fail the update
            error_log('Failed to log immunization recording activity: ' . $e->getMessage());
        }
    }

    if ($batch_schedule_changed) {
        try {
            $before = $previous_batch_schedule ?: 'none';
            $after = $update['batch_schedule_date'] ?? 'none';
            supabaseLogActivity(
                $actor_id,
                $actor_type,
                'BATCH_SCHEDULE_UPDATE',
                sprintf(
                    '%s set batch schedule for %s (Dose %s) from %s to %s (Baby ID: %s, Record ID: %s)',
                    $actor_name ?: ucfirst($actor_type),
                    $vaccine_name,
                    $dose_number,
                    $before,
                    $after,
                    $record['baby_id'],
                    $record['id']
                ),
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        } catch (Exception $e) {
            error_log('Failed to log batch schedule update: ' . $e->getMessage());
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


