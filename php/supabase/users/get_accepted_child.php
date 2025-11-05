<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$user_id = $_SESSION['user_id'];


// Get child health records for the logged-in user
$child_columns = 'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,status,qr_code';
$child_records = supabaseSelect('child_health_records', $child_columns, ['user_id' => $user_id], 'date_created.desc');

$rows = [];

if ($child_records && count($child_records) > 0) {
    // Get all baby_ids for batch query
    $baby_ids = array_column($child_records, 'baby_id');
    
    // Get ALL immunization records for ALL children in ONE query
    $all_immunizations = [];
    if (!empty($baby_ids)) {
        $immunization_columns = 'baby_id,vaccine_name,dose_number,status,schedule_date,date_given,catch_up_date';
        $all_immunizations = supabaseSelect('immunization_records', $immunization_columns, ['baby_id' => $baby_ids], 'schedule_date.desc');
    }
    
    // Group immunizations by baby_id for faster lookup
    $immunizations_by_baby = [];
    if ($all_immunizations) {
        foreach ($all_immunizations as $immunization) {
            $immunizations_by_baby[$immunization['baby_id']][] = $immunization;
        }
    }
    
    foreach ($child_records as $child) {
        // Calculate age from birth date
        $birth_date = new DateTime($child['child_birth_date']);
        $current_date = new DateTime();
        $weeks_old = $current_date->diff($birth_date)->days / 7;

        $age = $current_date->diff($birth_date)->y;
        
        // Get immunization records for this child from pre-loaded data
        $immunization_records = $immunizations_by_baby[$child['baby_id']] ?? [];
        
        // Count vaccination statuses
        $taken_count = 0;
        $missed_count = 0;
        $scheduled_count = 0;
        $upcoming_schedule = '';
        $upcoming_vaccine = '';
        $latest_vaccine = '';
        $latest_dose = '';
        
        if ($immunization_records && count($immunization_records) > 0) {
            $current_date_str = $current_date->format('Y-m-d');
            
            // First, count all statuses
            foreach ($immunization_records as $immunization) {
                if ($immunization['status'] === 'taken') {
                    $taken_count++;
                } elseif ($immunization['status'] === 'missed') {
                    $missed_count++;
                } elseif ($immunization['status'] === 'scheduled') {
                    $scheduled_count++;
                }
            }
            
            // Now find the closest upcoming vaccination
            $upcoming_vaccinations = array_filter($immunization_records, function($immunization) use ($current_date_str) {
                return $immunization['status'] === 'scheduled' && 
                       ($immunization['schedule_date'] >= $current_date_str || 
                        $immunization['catch_up_date'] >= $current_date_str);
            });
            
            if (!empty($upcoming_vaccinations)) {
                // Sort by schedule_date or catch_up_date to find the closest one
                usort($upcoming_vaccinations, function($a, $b) use ($current_date_str) {
                    $dateA = $a['schedule_date'] ?: $a['catch_up_date'];
                    $dateB = $b['schedule_date'] ?: $b['catch_up_date'];
                    return strtotime($dateA) - strtotime($dateB);
                });
                
                $closest_upcoming = $upcoming_vaccinations[0];
                $upcoming_schedule = $closest_upcoming['schedule_date'] ?: $closest_upcoming['catch_up_date'];
                $upcoming_vaccine = $closest_upcoming['vaccine_name'] ?: '';
            }
            
            // Get the latest vaccine and dose information
            $latest_immunization = end($immunization_records);
            $latest_vaccine = $latest_immunization['vaccine_name'] ?: '';
            $latest_dose = $latest_immunization['dose_number'] ?: '';
        }
        

        $rows[] = [
            'id' => $child['id'],
            'baby_id' => $child['baby_id'],
            'name' => $child['child_fname'] . ' ' . $child['child_lname'],
            'age' => $age,
            'weeks_old' => round($weeks_old, 1),
            'gender' => $child['child_gender'],
            'vaccine' => $upcoming_vaccine ?: $latest_vaccine, // Use upcoming vaccine if available, otherwise latest
            'dose' => $latest_dose,
            'schedule_date' => $upcoming_schedule,
            'status' => $child['status'],
            'qr_code' => $child['qr_code'] ?? null, // Add QR code
            // Vaccination counts
            'taken_count' => $taken_count,
            'missed_count' => $missed_count,
            'scheduled_count' => $scheduled_count
        ];
    }
}

echo json_encode(['status'=>'success','data'=>$rows ?: []]);


?>


