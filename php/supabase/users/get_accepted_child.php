<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$user_id = $_SESSION['user_id'];


// Get child health records for the logged-in user
$child_columns = 'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,status';
$child_records = supabaseSelect('child_health_records', $child_columns, ['user_id' => $user_id], 'date_created.desc');

$rows = [];

if ($child_records && count($child_records) > 0) {
    foreach ($child_records as $child) {
        // Calculate age from birth date
        $birth_date = new DateTime($child['child_birth_date']);
        $current_date = new DateTime();
        $weeks_old = $current_date->diff($birth_date)->days / 7;

        $age = $current_date->diff($birth_date)->y;
        
        // Get immunization records for this child
        $immunization_columns = 'id,baby_id,vaccine_name,dose_number,status,schedule_date,date_given,catch_up_date';
        $immunization_records = supabaseSelect('immunization_records', $immunization_columns, ['baby_id' => $child['baby_id']], 'schedule_date.desc');
        
        // Get the next upcoming vaccination (status = 'pending' or future catch_up_date)
        $upcoming_schedule = '';
        if ($immunization_records && count($immunization_records) > 0) {
            $current_date_str = $current_date->format('Y-m-d');
            foreach ($immunization_records as $immunization) {
                if ($immunization['status'] === 'pending' || 
                    ($immunization['schedule_date'] && $immunization['schedule_date'] >= $current_date_str)) {
                    $upcoming_schedule = $immunization['schedule_date'] ?: 'Pending';
                    break;
                }
            }
        }
        
        // Get the latest vaccine and dose information
        $latest_vaccine = '';
        $latest_dose = '';
        if ($immunization_records && count($immunization_records) > 0) {
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
            'vaccine' => $latest_vaccine,
            'dose' => $latest_dose,
            'schedule_date' => $upcoming_schedule,
            'status' => $child['status']
        ];
    }
}

echo json_encode(['status'=>'success','data'=>$rows ?: []]);


?>


