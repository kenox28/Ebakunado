<?php
session_start();
header('Content-Type: application/json');

// Handle both BHW and Midwife sessions
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? 'bhw';

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - User ID not found in session']);
    exit();
}

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

// Get form data
$child_fname = $_POST['child_fname'] ?? '';
$child_lname = $_POST['child_lname'] ?? '';
$child_gender = $_POST['child_gender'] ?? '';
$child_birth_date = $_POST['child_birth_date'] ?? '';
$place_of_birth = $_POST['place_of_birth'] ?? '';
$mother_name = $_POST['mother_name'] ?? '';
$father_name = $_POST['father_name'] ?? '';
$birth_weight = $_POST['birth_weight'] ?? null;
$birth_height = $_POST['birth_height'] ?? null;
$birth_attendant = $_POST['birth_attendant'] ?? '';
$delivery_type = $_POST['delivery_type'] ?? '';
$birth_order = $_POST['birth_order'] ?? '';
$birth_attendant_others = $_POST['birth_attendant_others'] ?? '';
$vaccines_received = $_POST['vaccines_received'] ?? [];

// Optional new fields
$blood_type = $_POST['blood_type'] ?? '';
$allergies = $_POST['allergies'] ?? '';
$lpm = $_POST['lpm'] ?? null;
$family_planning = $_POST['family_planning'] ?? '';

// Get address dropdown values
$province = $_POST['province'] ?? '';
$city_municipality = $_POST['city_municipality'] ?? '';
$barangay = $_POST['barangay'] ?? '';
$purok = $_POST['purok'] ?? '';

// Create standardized address format
$child_address = trim($province . ", " . $city_municipality . ", " . $barangay . ($purok ? ", " . $purok : ""));

// Validate required fields
if (empty($child_fname) || empty($child_lname) || empty($child_gender) || empty($child_birth_date) || empty($province) || empty($city_municipality) || empty($barangay)) {
    echo json_encode(['status' => 'error', 'message' => 'Required fields are missing (including Province, City, and Barangay)']);
    exit();
}

// Handle birth_attendant field - if "Others" is selected, use the others text field
if ($birth_attendant === 'Others' && !empty($birth_attendant_others)) {
    $birth_attendant = $birth_attendant_others;
}

// Generate unique family code
do {
    $family_code = 'FAM-' . strtoupper(substr(md5(uniqid()), 0, 6));
    $exists = supabaseSelect('child_health_records', 'id', ['user_id' => $family_code]);
} while ($exists);

// Generate baby_id
$baby_id = 'BABY' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

// Insert record with family code in user_id column
$insert = supabaseInsert('child_health_records', [
    'user_id' => $family_code,  // Store family code in user_id temporarily
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
    'delivery_type' => $delivery_type,
    'birth_order' => $birth_order,
    'blood_type' => $blood_type !== '' ? $blood_type : null,
    'allergies' => $allergies !== '' ? $allergies : null,
    'lpm' => $lpm ?: null,
    'family_planning' => $family_planning !== '' ? $family_planning : null,
    'status' => 'pending',
    'date_created' => date('Y-m-d H:i:s')
]);

if ($insert !== false) {
    // Create immunization records for the child
    createImmunizationRecords($baby_id, $child_birth_date, $vaccines_received);
    
    // Generate QR code automatically
    $qrSecureUrl = null;
    try {
        require_once __DIR__ . '/../../../vendor/autoload.php';
        $cloudinaryConfig = include __DIR__ . '/../../../assets/config/cloudinary.php';

        \Cloudinary\Configuration\Configuration::instance([
            'cloud' => [
                'cloud_name' => $cloudinaryConfig['cloud_name'],
                'api_key' => $cloudinaryConfig['api_key'],
                'api_secret' => $cloudinaryConfig['api_secret']
            ],
            'url' => ['secure' => $cloudinaryConfig['secure']]
        ]);

        // Generate QR with baby_id
        $qrData = $baby_id;
        $qrServiceUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&ecc=H&qzone=2&data=' . rawurlencode($qrData);

        $uploader = new \Cloudinary\Api\Upload\UploadApi();
        $uploadResult = $uploader->upload($qrServiceUrl, [
            'folder' => 'ebakunado/qr_codes',
            'public_id' => 'baby_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $baby_id),
            'overwrite' => true,
            'resource_type' => 'image'
        ]);

        $qrSecureUrl = $uploadResult['secure_url'] ?? ($uploadResult['url'] ?? null);
        if (!empty($qrSecureUrl)) {
            // Save QR URL to the record
            supabaseUpdate('child_health_records', ['qr_code' => $qrSecureUrl], ['baby_id' => $baby_id]);
        }
    } catch (Exception $e) {
        error_log('QR generation failed during child creation: ' . $e->getMessage());
        // Don't fail the entire process if QR generation fails
    }
    
    $share_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/../../views/users/Request.php?family_code=" . $family_code;
    
        echo json_encode([
            'status' => 'success',
            'family_code' => $family_code,
            'baby_id' => $baby_id,
            'qr_code' => $qrSecureUrl,
            'share_link' => $share_link,
            'message' => 'Child added successfully'
        ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add child']);
}

function createImmunizationRecords($baby_id, $birth_date, $vaccines_received) {
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
    
    $doseNum = 1;
    foreach ($vaccines as $vaccine) {
        $vname = $vaccine[0];
        $sched = $vaccine[1];
        $due = compute_due_date($birth_date, $sched);
        
        // Check if this vaccine was marked as received by BHW
        $is_transferred = in_array($vname, $vaccines_received);
        
        supabaseInsert('immunization_records', [
            'baby_id' => $baby_id,
            'vaccine_name' => $vname,
            'dose_number' => $doseNum,
            'status' => $is_transferred ? 'taken' : 'scheduled',
            'schedule_date' => $due,
            'catch_up_date' => null,
            'date_given' => $is_transferred ? $due : null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $doseNum++;
    }
}

function compute_due_date($birth, $sched) {
    $d = new DateTime($birth);
    $s = strtolower($sched);
    if (strpos($s, 'birth') !== false) return $d->format('Y-m-d');
    if (preg_match('/(\d+)\s*weeks?/', $s, $m)) { $d->modify('+' . intval($m[1]) . ' week'); return $d->format('Y-m-d'); }
    if (preg_match('/(\d+)\s*months?/', $s, $m)) { $d->modify('+' . intval($m[1]) . ' month'); return $d->format('Y-m-d'); }
    return $d->format('Y-m-d');
}
?>
