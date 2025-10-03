<?php
session_start();

include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
require_once '../../../vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

// Load Cloudinary config
$cloudinaryConfig = include '../../../assets/config/cloudinary.php';
Configuration::instance([
    'cloud' => [
        'cloud_name' => $cloudinaryConfig['cloud_name'], 
        'api_key' => $cloudinaryConfig['api_key'], 
        'api_secret' => $cloudinaryConfig['api_secret']
    ],
    'url' => ['secure' => $cloudinaryConfig['secure']]
]);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$child_fname = $_POST['child_fname'] ?? '';
$child_lname = $_POST['child_lname'] ?? '';
$child_gender = $_POST['child_gender'] ?? '';
$child_birth_date = $_POST['child_birth_date'] ?? '';
$place_of_birth = $_POST['place_of_birth'] ?? '';
$mother_name = $_POST['mother_name'] ?? '';
$father_name = $_POST['father_name'] ?? '';
$child_address = $_POST['child_address'] ?? '';
$birth_weight = $_POST['birth_weight'] ?? null;
$birth_height = $_POST['birth_height'] ?? null;
$birth_attendant = $_POST['birth_attendant'] ?? '';
$vaccines_received = $_POST['vaccines_received'] ?? [];

// Child History fields
$delivery_type = $_POST['delivery_type'] ?? '';
$birth_order = $_POST['birth_order'] ?? '';
$birth_attendant_others = $_POST['birth_attendant_others'] ?? '';

// Handle birth_attendant field - if "Others" is selected, use the others text field
if ($birth_attendant === 'Others' && !empty($birth_attendant_others)) {
    $birth_attendant = $birth_attendant_others;
}

// Generate baby_id
$baby_id = 'BABY' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

// Handle file upload to Cloudinary
$babys_card = null;
$upload_status = 'no_file';
$cloudinary_debug = [];

if (isset($_FILES['babys_card']) && $_FILES['babys_card']['error'] === UPLOAD_ERR_OK) {
    try {
        $uploadApi = new UploadApi();
        $public_id = 'baby_cards/baby_card_' . $user_id . '_' . time();
        $result = $uploadApi->upload($_FILES['babys_card']['tmp_name'], [
            'public_id' => $public_id,
            'folder' => 'ebakunado/baby_cards',
            'resource_type' => 'auto'
        ]);
        $babys_card = $result['secure_url'];
        $upload_status = 'success';
        $cloudinary_debug = [
            'public_id' => $result['public_id'],
            'secure_url' => $result['secure_url'],
            'format' => $result['format'],
            'bytes' => $result['bytes']
        ];
        error_log('Cloudinary upload successful: ' . $result['secure_url']);
    } catch (Exception $e) {
        $upload_status = 'failed';
        $cloudinary_debug = ['error' => $e->getMessage()];
        error_log('Cloudinary upload failed: ' . $e->getMessage());
    }
}

// Insert into child_health_records via Supabase
$insert = supabaseInsert('child_health_records', [
    'user_id' => $user_id,
    'baby_id' => $baby_id,
    'child_fname' => $child_fname,
    'child_lname' => $child_lname,
    'child_gender' => $child_gender,
    'child_birth_date' => $child_birth_date,
    'place_of_birth' => $place_of_birth,
    'mother_name' => $mother_name,
    'father_name' => $father_name,
    'address' => $child_address,
    'birth_weight' => $birth_weight,
    'birth_height' => $birth_height,
    'birth_attendant' => $birth_attendant,
    'babys_card' => $babys_card,
    'delivery_type' => $delivery_type,
    'birth_order' => $birth_order
]);

if ($insert === false) {
    $err = isset($supabase) && method_exists($supabase, 'getLastError') ? $supabase->getLastError() : null;
    echo json_encode(['status' => 'error', 'message' => 'Insert failed', 'debug' => $err]);
    exit();
}

// Create ALL immunization records (transferred + scheduled)
$vaccines = [
    ['BCG', 'at birth'],
    ['HEPAB1 (w/in 24 hrs)', 'at birth'],
    ['HEPAB1 (More than 24hrs)', 'at birth'],
    ['Pentavalent (DPT-HepB-Hib) - 1st', '6 weeks'],
    ['OPV - 1st', '6 weeks'],
    ['PCV - 1st', '6 weeks'],
    ['Rota Virus Vaccine - 1st', '6 weeks'],
    ['Pentavalent (DPT-HepB-Hib) - 2nd', '10 weeks'],
    ['OPV - 2nd', '10 weeks'],
    ['PCV - 2nd', '10 weeks'],
    ['Rota Virus Vaccine - 2nd', '10 weeks'],
    ['Pentavalent (DPT-HepB-Hib) - 3rd', '14 weeks'],
    ['OPV - 3rd', '14 weeks'],
    ['PCV - 3rd', '14 weeks'],
    ['MCV1 (AMV)', '9 months'],
    ['MCV2 (MMR)', '12 months']
];

// Helper to compute due date (catch_up_date)
function compute_due_date($birth, $sched) {
    $d = new DateTime($birth);
    $s = strtolower($sched);
    if (strpos($s, 'birth') !== false) return $d->format('Y-m-d');
    if (preg_match('/(\d+)\s*weeks?/', $s, $m)) { $d->modify('+' . intval($m[1]) . ' week'); return $d->format('Y-m-d'); }
    if (preg_match('/(\d+)\s*months?/', $s, $m)) { $d->modify('+' . intval($m[1]) . ' month'); return $d->format('Y-m-d'); }
    return $d->format('Y-m-d');
}

$immunization_records_created = 0;
$transferred_count = 0;
$doseNum = 1;

foreach ($vaccines as $v) {
    $vname = $v[0];
    $sched = $v[1];
    $due = compute_due_date($child_birth_date, $sched);
    
    // Check if this vaccine was marked as received by user
    $is_transferred = in_array($vname, $vaccines_received);
    
    $immunization_insert = supabaseInsert('immunization_records', [
        'baby_id' => $baby_id,
        'vaccine_name' => $vname,
        'dose_number' => $doseNum,
        'status' => $is_transferred ? 'taken' : 'scheduled',
        'schedule_date' => $due,
        'catch_up_date' => null,
        'date_given' => $is_transferred ? $due : null,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    if ($immunization_insert !== false) {
        $immunization_records_created++;
        if ($is_transferred) {
            $transferred_count++;
        }
    }
    $doseNum++;
}

echo json_encode([
    'status' => 'success', 
    'message' => 'Child health record saved successfully',
    'upload_status' => $upload_status,
    'cloudinary_info' => $cloudinary_debug,
    'baby_id' => $baby_id,
    'vaccines_transferred' => $transferred_count,
    'vaccines_scheduled' => $immunization_records_created - $transferred_count,
    'total_records_created' => $immunization_records_created
]);

?>

