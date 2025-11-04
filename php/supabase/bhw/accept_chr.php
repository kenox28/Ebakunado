<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

$baby_id = $_POST['baby_id'] ?? '';
if ($baby_id === '') { echo json_encode(['status'=>'error','message'=>'Missing baby_id']); exit(); }

// Check authorization
if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

// Get approver info
$approver_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$approver_type = isset($_SESSION['midwife_id']) ? 'midwife' : 'bhw';
$approver_name = ($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '');

// Get child info for logging
$child_info = supabaseSelect('child_health_records', 'child_fname,child_lname,user_id,mother_name', ['baby_id' => $baby_id], null, 1);
$child_name = 'Unknown Child';
$mother_name = 'Unknown Mother';
$requesting_user_id = null;
if ($child_info && count($child_info) > 0) {
    $child_name = trim(($child_info[0]['child_fname'] ?? '') . ' ' . ($child_info[0]['child_lname'] ?? ''));
    $mother_name = $child_info[0]['mother_name'] ?? 'Unknown Mother';
    $requesting_user_id = $child_info[0]['user_id'] ?? null;
}

// 1) Mark CHR accepted
$ok = supabaseUpdate('child_health_records', [
	'status' => 'accepted', 
	'date_updated' => date('Y-m-d H:i:s')
], ['baby_id' => $baby_id]);
if ($ok === false) { echo json_encode(['status' => 'error', 'message' => 'Record not accepted']); exit(); }

// Log activity: BHW/Midwife approved child registration
try {
    supabaseLogActivity(
        $approver_id,
        $approver_type,
        'CHILD_REGISTRATION_APPROVED',
        $approver_name . ' approved child registration for ' . $child_name . ', child of ' . $mother_name . ' (Baby ID: ' . $baby_id . ')',
        $_SERVER['REMOTE_ADDR'] ?? null
    );
} catch (Exception $e) {
    // Log error but don't fail the approval
    error_log('Failed to log child registration approval activity: ' . $e->getMessage());
}

// 2) Generate QR code and upload to Cloudinary, then store URL in child_health_records.qr_code
try {
	// Load Cloudinary SDK
	require_once '../../../vendor/autoload.php';
	$cloudinaryConfig = include '../../../assets/config/cloudinary.php';

	\Cloudinary\Configuration\Configuration::instance([
		'cloud' => [
			'cloud_name' => $cloudinaryConfig['cloud_name'],
			'api_key' => $cloudinaryConfig['api_key'],
			'api_secret' => $cloudinaryConfig['api_secret']
		],
		'url' => ['secure' => $cloudinaryConfig['secure']]
	]);

	// Build QR content: encode only the raw baby_id (not a full URL)
	$qrData = $baby_id;

	// Generate higher-quality QR image via external service (bigger, high ECC)
	// size=600x600, ecc=H (high error correction), qzone=2 (quiet zone)
	$qrServiceUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&ecc=H&qzone=2&data=' . rawurlencode($qrData);

	// Upload QR image to Cloudinary by remote fetch
	$uploader = new \Cloudinary\Api\Upload\UploadApi();
	$uploadResult = $uploader->upload($qrServiceUrl, [
		'folder' => 'ebakunado/qr_codes',
		'public_id' => 'baby_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $baby_id),
		'overwrite' => true,
		'resource_type' => 'image'
	]);

	$qrSecureUrl = $uploadResult['secure_url'] ?? ($uploadResult['url'] ?? null);
	if (!empty($qrSecureUrl)) {
		// Save QR URL into record
		supabaseUpdate('child_health_records', ['qr_code' => $qrSecureUrl], ['baby_id' => $baby_id]);
	}
} catch (Exception $e) {
	// Fail silently for QR generation so acceptance still proceeds
	error_log('QR generation/upload failed: ' . $e->getMessage());
}

echo json_encode(['status' => 'success', 'message' => 'Record accepted successfully']);
?>


