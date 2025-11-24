<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

// Set JSON header early
header('Content-Type: application/json');

// Start output buffering to catch any unwanted output
ob_start();

try {
    include '../../../database/SupabaseConfig.php';
    include '../../../database/DatabaseHelper.php';
    require_once '../../../vendor/autoload.php';
} catch (Throwable $e) {
    error_log('Error loading includes: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load required files: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    exit();
}

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

// Helper function to send JSON response and exit
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    ob_clean(); // Clear any output buffer
    echo json_encode($data);
    exit();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
}

$user_id = $_SESSION['user_id'];

try {
    // Get POST data
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

    // Optional new fields
    $blood_type = $_POST['blood_type'] ?? '';
    $allergies = $_POST['allergies'] ?? '';
    $lpm = $_POST['lpm'] ?? null;
    $family_planning = $_POST['family_planning'] ?? '';
    $date_newbornScreening = $_POST['date_newbornScreening'] ?? null;
    $placeNewbornScreening = $_POST['placeNewbornScreening'] ?? '';

    // Child History fields
    $delivery_type = $_POST['delivery_type'] ?? '';
    $birth_order = $_POST['birth_order'] ?? '';
    $birth_attendant_others = $_POST['birth_attendant_others'] ?? '';

    // Handle birth_attendant field
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

    // Prepare insert data
    $insertData = [
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
        'birth_order' => $birth_order,
        'blood_type' => $blood_type !== '' ? $blood_type : null,
        'allergies' => $allergies !== '' ? $allergies : null,
        'lpm' => $lpm ?: null,
        'family_planning' => $family_planning !== '' ? $family_planning : null,
        'date_newbornscreening' => $date_newbornScreening !== '' && $date_newbornScreening !== null ? $date_newbornScreening : null,
        'placenewbornscreening' => trim($placeNewbornScreening) !== '' ? trim($placeNewbornScreening) : null,
        'status' => 'pending',
        'date_created' => date('Y-m-d H:i:s')
    ];

    // Log insert data for debugging
    error_log('Insert data: ' . json_encode($insertData, JSON_PRETTY_PRINT));

    // Insert into child_health_records via Supabase
    $insert = supabaseInsert('child_health_records', $insertData);

    if ($insert === false) {
        $err = null;
        try {
            $supabase = getSupabase();
            if ($supabase && method_exists($supabase, 'getLastError')) {
                $err = $supabase->getLastError();
            }
        } catch (Exception $e) {
            error_log('Error getting Supabase error: ' . $e->getMessage());
        }
        
        error_log('Insert failed. Error: ' . ($err ? json_encode($err) : 'Unknown error'));
        error_log('Insert data was: ' . json_encode($insertData, JSON_PRETTY_PRINT));
        
        sendJsonResponse([
            'status' => 'error', 
            'message' => 'Insert failed: ' . ($err ? json_encode($err) : 'Unknown database error'),
            'debug' => [
                'error' => $err,
                'insert_data' => $insertData,
                'baby_id' => $baby_id,
                'user_id' => $user_id
            ]
        ], 500);
    }

    // Log activity (wrap in try-catch to prevent failure)
    try {
        $user_info = supabaseSelect('users', 'fname,lname', ['user_id' => $user_id], null, 1);
        $user_name = 'User';
        if ($user_info && count($user_info) > 0) {
            $user_name = trim(($user_info[0]['fname'] ?? '') . ' ' . ($user_info[0]['lname'] ?? ''));
        }
        
        $child_name = trim($child_fname . ' ' . $child_lname);
        $mother_name_clean = trim($mother_name) ?: 'Unknown Mother';
        supabaseLogActivity(
            $user_id,
            'user',
            'CHILD_REGISTRATION_REQUEST',
            $user_name . ' submitted child registration for ' . $child_name . ', child of ' . $mother_name_clean . ' (Baby ID: ' . $baby_id . ')',
            $_SERVER['REMOTE_ADDR'] ?? null
        );
    } catch (Exception $e) {
        // Log error but don't fail the request
        error_log('Failed to log child registration request activity: ' . $e->getMessage());
    }


    $vaccines = [
        ['BCG', 'at birth'],
        ['Hepatitis B', 'at birth'],
        ['Pentavalent (DPT-HepB-Hib) - 1st', '6 weeks'],
        ['OPV - 1st', '6 weeks'],
        ['PCV - 1st', '6 weeks'],
        ['Pentavalent (DPT-HepB-Hib) - 2nd', '10 weeks'],
        ['OPV - 2nd', '10 weeks'],
        ['PCV - 2nd', '10 weeks'],
        ['Pentavalent (DPT-HepB-Hib) - 3rd', '14 weeks'],
        ['OPV - 3rd', '14 weeks'],
        ['IPV', '14 weeks'],
        ['PCV - 3rd', '14 weeks'],
        ['MCV1 (AMV)', '9 months'],
        ['MCV2 (MMR)', '12 months']
    ];

    // Helper to compute due date
    function compute_due_date($birth, $sched) {
        $d = new DateTime($birth);
        $s = strtolower($sched);
        if (strpos($s, 'birth') !== false) return $d->format('Y-m-d');
        if (preg_match('/(\d+)\s*weeks?/', $s, $m)) { 
            $d->modify('+' . intval($m[1]) . ' week'); 
            return $d->format('Y-m-d'); 
        }
        if (preg_match('/(\d+)\s*months?/', $s, $m)) { 
            $d->modify('+' . intval($m[1]) . ' month'); 
            return $d->format('Y-m-d'); 
        }
        return $d->format('Y-m-d');
    }

    $immunization_records_created = 0;
    $transferred_count = 0;

    // Derive dose number per vaccine series (not global incremental)
    function derive_dose_number($vname) {
        $n = strtoupper((string)$vname);
        if (strpos($n, ' - 1ST') !== false) return 1;
        if (strpos($n, ' - 2ND') !== false) return 2;
        if (strpos($n, ' - 3RD') !== false) return 3;
        if (strpos($n, 'MCV1') !== false || strpos($n, '(AMV)') !== false) return 1;
        if (strpos($n, 'MCV2') !== false || strpos($n, '(MMR)') !== false) return 2;
        if (strpos($n, 'BCG') !== false) return 1;
        if (strpos($n, 'HEP') !== false) return 1;
        if (strpos($n, 'IPV') !== false) return 1;
        return 1;
    }

    // Parse vaccines_received if it's a string
    if (is_string($vaccines_received)) {
        $vaccines_received = !empty($vaccines_received) ? explode(',', $vaccines_received) : [];
    }

    foreach ($vaccines as $v) {
        try {
            $vname = $v[0];
            $sched = $v[1];
            $due = compute_due_date($child_birth_date, $sched);
            
            // Check if this vaccine was marked as received by user
            $is_transferred = in_array($vname, $vaccines_received);
            
            $immunization_insert = supabaseInsert('immunization_records', [
                'baby_id' => $baby_id,
                'vaccine_name' => $vname,
                'dose_number' => derive_dose_number($vname),
                'status' => $is_transferred ? 'taken' : 'scheduled',
                'schedule_date' => $due,
                'batch_schedule_date' => null,
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
        } catch (Exception $e) {
            // Log error but continue with other vaccines
            error_log('Failed to insert vaccine record: ' . $e->getMessage());
        }
    }

    // Always return JSON response, even if some operations failed
    sendJsonResponse([
        'status' => 'success', 
        'message' => 'Child health record saved successfully',
        'upload_status' => $upload_status,
        'cloudinary_info' => $cloudinary_debug,
        'baby_id' => $baby_id,
        'child_name' => trim($child_fname . ' ' . $child_lname), // Add child name
        'vaccines_transferred' => $transferred_count,
        'vaccines_scheduled' => $immunization_records_created - $transferred_count,
        'total_records_created' => $immunization_records_created
    ]);

} catch (Throwable $e) {
    // Catch any unexpected errors and return JSON error response
    $errorMsg = $e->getMessage();
    $errorFile = basename($e->getFile());
    $errorLine = $e->getLine();
    $errorTrace = $e->getTraceAsString();
    
    error_log('Child registration error: ' . $errorMsg);
    error_log('Error file: ' . $e->getFile() . ':' . $errorLine);
    error_log('Stack trace: ' . $errorTrace);
    
    sendJsonResponse([
        'status' => 'error',
        'message' => 'An error occurred while processing your request: ' . $errorMsg,
        'debug' => [
            'error_message' => $errorMsg,
            'error_file' => $errorFile,
            'error_line' => $errorLine,
            'error_type' => get_class($e),
            'stack_trace' => explode("\n", $errorTrace)
        ]
    ], 500);
}
?>