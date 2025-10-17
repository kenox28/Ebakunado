<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

$baby_id = $_POST['baby_id'] ?? '';
if ($baby_id === '') { echo json_encode(['status'=>'error','message'=>'Missing baby_id']); exit(); }

// 1) Mark CHR accepted
$ok = supabaseUpdate('child_health_records', [
	'status' => 'accepted', 
	'date_updated' => date('Y-m-d H:i:s')
], ['baby_id' => $baby_id]);
if ($ok === false) { echo json_encode(['status' => 'error', 'message' => 'Record not accepted']); exit(); }

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


