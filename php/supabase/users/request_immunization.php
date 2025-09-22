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
    'babys_card' => $babys_card
]);

if ($insert === false) {
    $err = isset($supabase) && method_exists($supabase, 'getLastError') ? $supabase->getLastError() : null;
    echo json_encode(['status' => 'error', 'message' => 'Insert failed', 'debug' => $err]);
    exit();
}

echo json_encode([
    'status' => 'success', 
    'message' => 'Child health record saved successfully',
    'upload_status' => $upload_status,
    'cloudinary_info' => $cloudinary_debug,
    'baby_id' => $baby_id
]);

?>

