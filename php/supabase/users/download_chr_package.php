<?php
/**
 * Download CHR Package: CHR PDF + filled baby card image
 * - Validates the requesting user
 * - Fetches the approved request and child data
 * - Downloads the generated CHR PDF from Cloudinary (or local path)
 * - Renders babycard.jpg with child details overlaid
 * - Streams both as a single ZIP archive
 */

session_start();

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

// Simple logger
$__dl_log_path = __DIR__ . '/../../../logs/download_chr_package_' . date('Y-m-d') . '.log';
function log_msg($msg){
	global $__dl_log_path;
	@file_put_contents($__dl_log_path, '[' . date('H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
}
log_msg('== New request == ' . ($_GET['request_id'] ?? 'no_id'));

// Basic auth check
if (!isset($_SESSION['user_id'])) {
	http_response_code(401);
	echo 'Unauthorized';
	exit();
}

// Helpers
function fail_with($code, $message){
	log_msg('FAIL ' . $code . ' ' . $message);
	http_response_code($code);
	echo $message;
	exit();
}

function fetch_remote_or_local($url){
	$isRemote = preg_match('/^https?:\/\//i', $url) === 1;
	if ($isRemote) {
		log_msg('Fetching remote: ' . $url);
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_USERAGENT, 'ebakunado-package-downloader');
			$bytes = curl_exec($ch);
			$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$err = curl_error($ch);
			curl_close($ch);
			if ($bytes === false || $http >= 400){
				throw new Exception('Remote fetch failed (HTTP ' . $http . '): ' . ($err ?: ''));
			}
			return $bytes;
		} else {
			// Fallback without cURL
			$context = stream_context_create([
				'http' => [
					'method' => 'GET',
					'timeout' => 30,
					'header' => "User-Agent: ebakunado-package-downloader\r\n"
				]
			]);
			$bytes = @file_get_contents($url, false, $context);
			if ($bytes === false){
				throw new Exception('allow_url_fopen fetch failed and cURL unavailable');
			}
			return $bytes;
		}
	}
	// Local path must be inside /ebakunado
	if (strpos($url, '/ebakunado/') !== 0) {
		throw new Exception('Invalid local URL');
	}
	$abs = $_SERVER['DOCUMENT_ROOT'] . $url;
	log_msg('Fetching local: ' . $abs);
	if (!file_exists($abs)) {
		throw new Exception('Local file not found');
	}
	$bytes = file_get_contents($abs);
	if ($bytes === false){
		throw new Exception('Failed to read local file');
	}
	return $bytes;
}

function percent_to_xy($im, $xpct, $ypct){
	$w = imagesx($im);
	$h = imagesy($im);
	$x = (int)round(($xpct/100.0) * $w);
	$y = (int)round(($ypct/100.0) * $h);
	return [$x, $y];
}

function draw_text($im, $text, $xpct, $ypct, $fontSizePct = 2.2){
	if ($text === null) $text = '';
	$text = (string)$text;
	$black = imagecolorallocate($im, 20, 30, 40);
	$shadow = imagecolorallocate($im, 255, 255, 255);
	$w = imagesx($im);
	$fontPx = max(10, (int)round(($fontSizePct/100.0) * $w));
	$fontFile = __DIR__ . '/../../../vendor/mpdf/mpdf/ttfonts/DejaVuSans.ttf';
	list($x, $y) = percent_to_xy($im, $xpct, $ypct);

	// If TTF font missing, fallback to built-in font
	if (!file_exists($fontFile)) {
		log_msg('Font file missing, fallback to GD built-in font');
		// GD built-in fonts are tiny; scale by writing multiple lines if long
		imagestring($im, 5, $x, $y - 12, $text, $black);
		return;
	}
	// Slight white shadow for readability
	imagettftext($im, $fontPx, 0, $x+1, $y+1, $shadow, $fontFile, $text);
	imagettftext($im, $fontPx, 0, $x, $y, $black, $fontFile, $text);
}

function render_babycard_image(array $child){
	$basePath = $_SERVER['DOCUMENT_ROOT'] . '/ebakunado/assets/images/babycard.jpg';
	if (!file_exists($basePath)) {
		throw new Exception('Base baby card image not found');
	}
	// Check GD availability
	if (!extension_loaded('gd') || !function_exists('imagecreatefromjpeg')) {
		log_msg('GD not available, returning base image without overlay');
		return $basePath; // fallback: return base image path (unfilled)
	}
	$im = imagecreatefromjpeg($basePath);
	if (!$im) {
		throw new Exception('Failed to open baby card image');
	}

	// Extract fields
	$childName = trim(($child['child_fname'] ?? '') . ' ' . ($child['child_lname'] ?? ''));
	$dob = $child['child_birth_date'] ?? '';
	if ($dob) { $dob = substr($dob, 0, 10); }
	$placeOfBirth = $child['place_of_birth'] ?? '';
	$address = $child['address'] ?? '';
	$motherName = $child['mother_name'] ?? '';
	$fatherName = $child['father_name'] ?? '';
	$birthHeight = $child['birth_height'] ?? '';
	$birthWeight = $child['birth_weight'] ?? '';
	$sex = strtoupper(substr((string)($child['child_gender'] ?? ''), 0, 1));
	if ($sex !== 'M' && $sex !== 'F') { $sex = ''; }

	// Left column fields (approximate positions, percentage-based)
	draw_text($im, $childName, 8.0, 22.0);
	draw_text($im, $dob, 8.0, 28.5);
	draw_text($im, $placeOfBirth, 8.0, 35.0);
	draw_text($im, $address, 8.0, 41.5);

	// Right column fields
	draw_text($im, $motherName, 58.0, 22.0);
	draw_text($im, $fatherName, 58.0, 28.5);
	draw_text($im, $birthHeight, 58.0, 35.0);
	draw_text($im, $birthWeight, 58.0, 41.5);

	// Sex marker
	if ($sex) {
		draw_text($im, $sex, 93.0, 41.5);
	}

	// Output to temp file
	$tmpJpg = tempnam(sys_get_temp_dir(), 'babycard_');
	$jpgPath = $tmpJpg . '.jpg';
	@unlink($tmpJpg);
	imagejpeg($im, $jpgPath, 90);
	imagedestroy($im);
	if (!file_exists($jpgPath) || filesize($jpgPath) === 0) {
		throw new Exception('Failed to render filled baby card');
	}
	return $jpgPath;
}

try {
	$requestId = $_GET['request_id'] ?? '';
	if ($requestId === '') {
		fail_with(400, 'Missing request_id');
	}
	$userId = $_SESSION['user_id'];

	// Load request and validate ownership
	$reqRows = supabaseSelect('chrdocrequest', '*', ['id' => $requestId], null, 1);
	if (!$reqRows || count($reqRows) === 0) {
		fail_with(404, 'Request not found');
	}
	$req = $reqRows[0];
	log_msg('Loaded request id=' . $requestId);
	if (($req['user_id'] ?? null) !== $userId) {
		fail_with(403, 'Forbidden');
	}
	if (($req['status'] ?? '') !== 'approved') {
		fail_with(409, 'Request not approved');
	}
	$docUrl = $req['doc_url'] ?? '';
	if ($docUrl === '') {
		fail_with(409, 'No document URL found');
	}

	// Download CHR PDF bytes
	log_msg('Fetching PDF from: ' . $docUrl);
	$pdfBytes = fetch_remote_or_local($docUrl);

	// Load child details
	$babyId = $req['baby_id'] ?? '';
	if ($babyId === '') {
		fail_with(409, 'Missing baby_id');
	}
	$childRows = supabaseSelect('child_health_records', '*', ['baby_id' => $babyId], null, 1);
	if (!$childRows || count($childRows) === 0) {
		fail_with(404, 'Child record not found');
	}
	$child = $childRows[0];
	log_msg('Loaded child for baby_id=' . $babyId);

	// Render baby card image with details
	$jpgPath = render_babycard_image($child);
	$jpgBytes = file_get_contents($jpgPath);
	// Only delete temp file if it's inside temp dir (not the static base)
	if (strpos($jpgPath, sys_get_temp_dir()) === 0) { @unlink($jpgPath); }

	// Build ZIP
	if (!class_exists('ZipArchive')) {
		log_msg('ZipArchive not available, sending PDF only');
		// Fallback: send PDF only to ensure some output
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="CHR_Document.pdf"');
		echo $pdfBytes;
		exit();
	}
	$zipTmp = tempnam(sys_get_temp_dir(), 'chrpkg_');
	$zipPath = $zipTmp . '.zip';
	@unlink($zipTmp);
	$zip = new ZipArchive();
	if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
		fail_with(500, 'Failed to create archive');
	}
	$zip->addFromString('CHR_Document.pdf', $pdfBytes);
	$zip->addFromString('Child_Immunization_Card.jpg', $jpgBytes);
	$zip->close();

	$filename = 'CHR_Package_' . $babyId . '_' . date('Y-m-d_H-i-s') . '.zip';
	header('Content-Type: application/zip');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Content-Length: ' . filesize($zipPath));
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
	readfile($zipPath);
	@unlink($zipPath);
	exit();
} catch (Exception $e) {
	log_msg('EXCEPTION: ' . $e->getMessage());
	http_response_code(500);
	echo 'Download failed: ' . $e->getMessage();
	exit();
}
?>


