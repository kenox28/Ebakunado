<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$baby_id = $_POST['baby_id'];
$columns = 'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,place_of_birth,mother_name,father_name,address,birth_weight,birth_height,birth_attendant,babys_card,delivery_type,birth_order,date_created:date_created,date_updated:date_updated,status,qr_code,exclusive_breastfeeding_1mo,exclusive_breastfeeding_2mo,exclusive_breastfeeding_3mo,exclusive_breastfeeding_4mo,exclusive_breastfeeding_5mo,exclusive_breastfeeding_6mo,complementary_feeding_6mo,complementary_feeding_7mo,complementary_feeding_8mo,mother_td_dose1_date,mother_td_dose2_date,mother_td_dose3_date,mother_td_dose4_date,mother_td_dose5_date';
$rows = supabaseSelect('child_health_records', $columns, ['baby_id' => $baby_id], 'date_created.desc');
$child_records = [];
if ($rows && count($rows) > 0) {
    foreach ($rows as $child) {
    $birth_date = new DateTime($child['child_birth_date']);
    $current_date = new DateTime();
    $weeks_old = $current_date->diff($birth_date)->days / 7;

    $age = $current_date->diff($birth_date)->y;

    // Fetch family number (use parent phone_number from users table)
    $family_number = '';
    if (!empty($child['user_id'])) {
        $urows = supabaseSelect('users', 'phone_number', ['user_id' => $child['user_id']], null, 1);
        if ($urows && count($urows) > 0) {
            $family_number = $urows[0]['phone_number'] ?? '';
        }
    }

$child_records[] = [
    'id' => $child['id'],
    'baby_id' => $child['baby_id'],
    'user_id' => $child['user_id'] ?? '',
    'name' => $child['child_fname'] . ' ' . $child['child_lname'],
    'child_fname' => $child['child_fname'],
    'child_lname' => $child['child_lname'],
    'child_gender' => $child['child_gender'],
    'child_birth_date' => $child['child_birth_date'],
    'place_of_birth' => $child['place_of_birth'],
    'mother_name' => $child['mother_name'],
    'father_name' => $child['father_name'],
    'address' => $child['address'],
    'birth_weight' => $child['birth_weight'],
    'birth_height' => $child['birth_height'],
    'birth_attendant' => $child['birth_attendant'],
    'delivery_type' => $child['delivery_type'] ?? '',
    'birth_order' => $child['birth_order'] ?? '',
    'family_number' => $family_number,
    'age' => $age,
    'weeks_old' => round($weeks_old, 1),
    'status' => $child['status'],
    'qr_code' => $child['qr_code'],
    // Exclusive Breastfeeding
    'exclusive_breastfeeding_1mo' => $child['exclusive_breastfeeding_1mo'] ?? false,
    'exclusive_breastfeeding_2mo' => $child['exclusive_breastfeeding_2mo'] ?? false,
    'exclusive_breastfeeding_3mo' => $child['exclusive_breastfeeding_3mo'] ?? false,
    'exclusive_breastfeeding_4mo' => $child['exclusive_breastfeeding_4mo'] ?? false,
    'exclusive_breastfeeding_5mo' => $child['exclusive_breastfeeding_5mo'] ?? false,
    'exclusive_breastfeeding_6mo' => $child['exclusive_breastfeeding_6mo'] ?? false,
    // Complementary Feeding
    'complementary_feeding_6mo' => $child['complementary_feeding_6mo'] ?? '',
    'complementary_feeding_7mo' => $child['complementary_feeding_7mo'] ?? '',
    'complementary_feeding_8mo' => $child['complementary_feeding_8mo'] ?? '',
    // Mother's TD Status
    'mother_td_dose1_date' => $child['mother_td_dose1_date'] ?? '',
    'mother_td_dose2_date' => $child['mother_td_dose2_date'] ?? '',
    'mother_td_dose3_date' => $child['mother_td_dose3_date'] ?? '',
    'mother_td_dose4_date' => $child['mother_td_dose4_date'] ?? '',
    'mother_td_dose5_date' => $child['mother_td_dose5_date'] ?? ''
];
}
}

echo json_encode(['status'=>'success','data'=>$child_records ?: []]);
?>
