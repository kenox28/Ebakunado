<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id'])) {
	echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
	exit();
}

$columns = 'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,place_of_birth,mother_name,father_name,address,birth_weight,birth_height,birth_attendant,babys_card,date_created,date_updated,status,exclusive_breastfeeding_1mo,exclusive_breastfeeding_2mo,exclusive_breastfeeding_3mo,exclusive_breastfeeding_4mo,exclusive_breastfeeding_5mo,exclusive_breastfeeding_6mo,complementary_feeding_6mo,complementary_feeding_7mo,complementary_feeding_8mo,mother_td_dose1_date,mother_td_dose2_date,mother_td_dose3_date,mother_td_dose4_date,mother_td_dose5_date';
$rows = supabaseSelect('child_health_records', $columns, ['status' => 'accepted'], 'date_created.desc');

echo json_encode(['status' => 'success', 'data' => $rows ?: []]);
?>


