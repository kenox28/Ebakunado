<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

// Handle both BHW and Midwife sessions
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - User ID not found in session']);
    exit();
}

try {
    // Get all child health records with user information
    $child_records = supabaseSelect(
        'child_health_records',
        'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,mother_name,father_name,address,birth_weight,birth_height,status',
        [],
        'child_birth_date.desc'
    );

    $tcl_data = [];

    if ($child_records) {
        foreach ($child_records as $index => $child) {
            // Get user information
            $user_info = supabaseSelect(
                'users',
                'fname,lname,phone_number',
                ['user_id' => $child['user_id']],
                null,
                1
            );

            // Get immunization records for this child
            $immunization_records = supabaseSelect(
                'immunization_records',
                'vaccine_name,dose_number,status,schedule_date,date_given,catch_up_date,weight,height',
                ['baby_id' => $child['baby_id']],
                'schedule_date.asc'
            );

            // Initialize vaccination status - using SCHEDULED, MISSED, TRANSFERRED
            $vaccination_status = [
                'BCG' => '',
                'HEPAB1_w_in_24hrs' => '',
                'HEPAB1_more_than_24hrs' => '',
                'Penta 1' => '',
                'Penta 2' => '',
                'Penta 3' => '',
                'OPV 1' => '',
                'OPV 2' => '',
                'OPV 3' => '',
                'Rota 1' => '',
                'Rota 2' => '',
                'PCV 1' => '',
                'PCV 2' => '',
                'PCV 3' => '',
                'MCV1_AMV' => '',
                'MCV2_MMR' => ''
            ];

            // Process immunization records
            if ($immunization_records) {
                foreach ($immunization_records as $record) {
                    $vaccine_key = '';
                    $date_given = '';
                    if (!empty($record['date_given'])) {
                        $date_timestamp = strtotime($record['date_given']);
                        if ($date_timestamp !== false) {
                            $date_given = date('m/d/Y', $date_timestamp);
                        } else {
                            // Fallback: try different date formats
                            $date_given = $record['date_given']; // Show raw date if formatting fails
                        }
                    }
                    
                    // Debug: Log what we're processing
                    error_log("Processing vaccine: " . $record['vaccine_name'] . " | Status: " . $record['status'] . " | Date Given: " . $record['date_given'] . " | Formatted Date: " . $date_given);
                    
                    // Map vaccine names to our keys - exact matches from request form
                    if ($record['vaccine_name'] === 'BCG') {
                        $vaccine_key = 'BCG';
                    } elseif ($record['vaccine_name'] === 'HEPAB1 (w/in 24 hrs)') {
                        $vaccine_key = 'HEPAB1_w_in_24hrs';
                    } elseif ($record['vaccine_name'] === 'HEPAB1 (More than 24hrs)') {
                        $vaccine_key = 'HEPAB1_more_than_24hrs';
                    } elseif ($record['vaccine_name'] === 'Pentavalent (DPT-HepB-Hib) - 1st') {
                        $vaccine_key = 'Penta 1';
                    } elseif ($record['vaccine_name'] === 'Pentavalent (DPT-HepB-Hib) - 2nd') {
                        $vaccine_key = 'Penta 2';
                    } elseif ($record['vaccine_name'] === 'Pentavalent (DPT-HepB-Hib) - 3rd') {
                        $vaccine_key = 'Penta 3';
                    } elseif ($record['vaccine_name'] === 'OPV - 1st') {
                        $vaccine_key = 'OPV 1';
                    } elseif ($record['vaccine_name'] === 'OPV - 2nd') {
                        $vaccine_key = 'OPV 2';
                    } elseif ($record['vaccine_name'] === 'OPV - 3rd') {
                        $vaccine_key = 'OPV 3';
                    } elseif ($record['vaccine_name'] === 'Rota Virus Vaccine - 1st') {
                        $vaccine_key = 'Rota 1';
                    } elseif ($record['vaccine_name'] === 'Rota Virus Vaccine - 2nd') {
                        $vaccine_key = 'Rota 2';
                    } elseif ($record['vaccine_name'] === 'PCV - 1st') {
                        $vaccine_key = 'PCV 1';
                    } elseif ($record['vaccine_name'] === 'PCV - 2nd') {
                        $vaccine_key = 'PCV 2';
                    } elseif ($record['vaccine_name'] === 'PCV - 3rd') {
                        $vaccine_key = 'PCV 3';
                    } elseif ($record['vaccine_name'] === 'MCV1 (AMV)') {
                        $vaccine_key = 'MCV1_AMV';
                    } elseif ($record['vaccine_name'] === 'MCV2 (MMR)') {
                        $vaccine_key = 'MCV2_MMR';
                    }

                    // Set status based on record - show checkmark with date if taken, X with catch-up date if missed
                    if (isset($vaccination_status[$vaccine_key])) {
                        if ($record['status'] === 'taken' && !empty($date_given)) {
                            $vaccination_status[$vaccine_key] = '✓ ' . $date_given;
                            error_log("Set " . $vaccine_key . " to: ✓ " . $date_given);
                        } elseif ($record['status'] === 'completed' && !empty($date_given)) {
                            $vaccination_status[$vaccine_key] = '✓ ' . $date_given;
                            error_log("Set " . $vaccine_key . " to: ✓ " . $date_given);
                        } elseif ($record['status'] === 'missed') {
                            $catch_up_date = !empty($record['catch_up_date']) ? date('m/d/Y', strtotime($record['catch_up_date'])) : '';
                            $vaccination_status[$vaccine_key] = '✗ ' . $catch_up_date;
                        } elseif ($record['status'] === 'scheduled') {
                            $vaccination_status[$vaccine_key] = 'SCHEDULED';
                        } elseif ($record['status'] === 'pending') {
                            $vaccination_status[$vaccine_key] = 'SCHEDULED';
                        }
                    } else {
                        error_log("Vaccine key not found: " . $vaccine_key . " for vaccine: " . $record['vaccine_name']);
                    }
                }
            }

            // Calculate current weight and height (use latest from immunization records)
            $current_weight = $child['birth_weight'];
            $current_height = $child['birth_height'];
            
            if ($immunization_records) {
                foreach ($immunization_records as $record) {
                    if (!empty($record['weight'])) {
                        $current_weight = $record['weight'];
                    }
                    if (!empty($record['height'])) {
                        $current_height = $record['height'];
                    }
                }
            }

            // Determine overall status - using SCHEDULED, MISSED, TRANSFERRED
            $overall_status = 'SCHEDULED';
            $missed_count = 0;
            $scheduled_count = 0;
            $completed_count = 0;
            
            foreach ($vaccination_status as $status) {
                if (strpos($status, '✗') !== false) {
                    $missed_count++;
                } elseif ($status === 'SCHEDULED') {
                    $scheduled_count++;
                } elseif (strpos($status, '✓') !== false) {
                    $completed_count++;
                }
            }
            
            if ($missed_count > 0) {
                $overall_status = 'MISSED';
            } elseif ($scheduled_count === 0) {
                $overall_status = 'TRANSFERRED'; // All vaccines completed
            }

            // Generate remarks
            $remarks = '';
            if ($missed_count > 0) {
                $remarks = $missed_count . ' vaccine(s) missed';
            } elseif ($scheduled_count > 0) {
                $remarks = $scheduled_count . ' vaccine(s) scheduled';
            } else {
                $remarks = 'All vaccines completed';
            }

            $tcl_data[] = [
                'id' => $index + 1,
                'child_name' => $child['child_fname'] . ' ' . $child['child_lname'],
                'sex' => $child['child_gender'],
                'date_of_birth' => date('m/d/Y', strtotime($child['child_birth_date'])),
                'mother_name' => $child['mother_name'],
                'address' => $child['address'],
                'BCG' => $vaccination_status['BCG'],
                'HEPAB1_w_in_24hrs' => $vaccination_status['HEPAB1_w_in_24hrs'],
                'HEPAB1_more_than_24hrs' => $vaccination_status['HEPAB1_more_than_24hrs'],
                'Penta 1' => $vaccination_status['Penta 1'],
                'Penta 2' => $vaccination_status['Penta 2'],
                'Penta 3' => $vaccination_status['Penta 3'],
                'OPV 1' => $vaccination_status['OPV 1'],
                'OPV 2' => $vaccination_status['OPV 2'],
                'OPV 3' => $vaccination_status['OPV 3'],
                'Rota 1' => $vaccination_status['Rota 1'],
                'Rota 2' => $vaccination_status['Rota 2'],
                'PCV 1' => $vaccination_status['PCV 1'],
                'PCV 2' => $vaccination_status['PCV 2'],
                'PCV 3' => $vaccination_status['PCV 3'],
                'MCV1_AMV' => $vaccination_status['MCV1_AMV'],
                'MCV2_MMR' => $vaccination_status['MCV2_MMR'],
                'weight' => $current_weight,
                'height' => $current_height,
                'status' => $overall_status,
                'remarks' => $remarks,
                'baby_id' => $child['baby_id'],
                'user_id' => $child['user_id'],
                'phone_number' => $user_info ? $user_info[0]['phone_number'] : ''
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => $tcl_data,
        'total_count' => count($tcl_data)
    ]);

} catch (Exception $e) {
    error_log('Error getting TCL data: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to get TCL data: ' . $e->getMessage()]);
}
?>
