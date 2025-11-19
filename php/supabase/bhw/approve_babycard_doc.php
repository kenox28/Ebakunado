<?php
// Enable error reporting and output buffering to catch fatal errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any errors
ob_start();

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'PHP Fatal Error: ' . $error['message'],
            'file' => basename($error['file']),
            'line' => $error['line'],
            'type' => $error['type']
        ]);
        exit();
    }
});

session_start();
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../../database/SupabaseConfig.php';
    require_once __DIR__ . '/../../../database/DatabaseHelper.php';
    require_once __DIR__ . '/../../../vendor/autoload.php';
    require_once __DIR__ . '/../shared/BabyCardPdfGenerator.php';
} catch (Throwable $e) {
    error_log('Error loading files in approve_babycard_doc.php: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to load required files: ' . $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
    exit();
}

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

try {
    if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
        throw new Exception('Unauthorized');
    }
    
    $request_id = $_POST['request_id'] ?? '';
    if (empty($request_id)) {
        throw new Exception('Missing request_id');
    }
    
    $reqRows = supabaseSelect('chrdocrequest', '*', ['id' => $request_id], null, 1);
    if (!$reqRows || count($reqRows) === 0) {
        throw new Exception('Request not found');
    }
    $req = $reqRows[0];
    
    // Verify it's a pending request (status='pendingCHR', request_type can be 'school' or 'transfer')
    if ($req['status'] !== 'pendingCHR') {
        throw new Exception('Request is not a pending request');
    }
    
    // Get child data
    $childRows = supabaseSelect('child_health_records', '*', ['baby_id' => $req['baby_id']], 'date_created.desc', 1);
    if (!$childRows || count($childRows) === 0) {
        throw new Exception('Child not found');
    }
    $child = $childRows[0];
    
    // Get immunization records (filter only taken/completed with date_given)
    $immunizationRows = supabaseSelect('immunization_records', '*', ['baby_id' => $req['baby_id']], 'date_given.asc');
    $immunizationData = [];
    if ($immunizationRows) {
        foreach ($immunizationRows as $imm) {
            if (!empty($imm['date_given'])) {
                $immunizationData[] = $imm;
            }
        }
    }
    
    // Generate Baby Card PDF using BabyCardPdfGenerator
    try {
        $pdfBytes = BabyCardPdfGenerator::generate($child, $immunizationData);
    } catch (Exception $e) {
        error_log('BabyCardPdfGenerator::generate Exception: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        throw new Exception('PDF generation failed: ' . $e->getMessage() . ' (File: ' . basename($e->getFile()) . ', Line: ' . $e->getLine() . ')');
    } catch (Error $e) {
        error_log('BabyCardPdfGenerator::generate PHP Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        throw new Exception('PDF generation PHP error: ' . $e->getMessage() . ' (File: ' . basename($e->getFile()) . ', Line: ' . $e->getLine() . ')');
    }
    
    if (empty($pdfBytes)) {
        throw new Exception('PDF generation failed - empty output');
    }
    
    // Save PDF to temporary file
    $tmp = tempnam(sys_get_temp_dir(), 'babycard_') . '.pdf';
    $written = file_put_contents($tmp, $pdfBytes);
    if ($written === false || !file_exists($tmp) || filesize($tmp) === 0) {
        throw new Exception('Failed to write PDF to temp file');
    }
    
    // Upload to Cloudinary
    $cloudinaryConfig = include __DIR__ . '/../../../assets/config/cloudinary.php';
    if (!$cloudinaryConfig) {
        throw new Exception('Cloudinary config not found');
    }
    
    Configuration::instance([
        'cloud' => [
            'cloud_name' => $cloudinaryConfig['cloud_name'],
            'api_key' => $cloudinaryConfig['api_key'],
            'api_secret' => $cloudinaryConfig['api_secret']
        ],
        'url' => ['secure' => $cloudinaryConfig['secure']]
    ]);
    
    // Upload generated PDF to Cloudinary (as raw file, but use 'image' resource_type for PDF)
    $uploadApi = new UploadApi();
    $publicId = 'babycard_' . $req['baby_id'] . '_' . time();
    $result = $uploadApi->upload($tmp, [
        'public_id' => $publicId,
        'folder' => 'ebakunado/babycard_docs',
        'resource_type' => 'raw' // Use 'raw' for PDF files
    ]);
    
    @unlink($tmp);
    
    if (!$result || empty($result['secure_url'])) {
        throw new Exception('Cloudinary upload failed');
    }
    $doc_url = $result['secure_url'];
    
    // Update database
    supabaseUpdate('chrdocrequest', [
        'status' => 'approved',
        'doc_url' => $doc_url,
        'approved_at' => date('Y-m-d H:i:s')
    ], ['id' => $request_id]);
    
    // Log activity: BHW/Midwife approved Baby Card document request
    try {
        // Get approver info
        $approver_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
        $approver_type = isset($_SESSION['midwife_id']) ? 'midwife' : 'bhw';
        $approver_name = ($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '');
        
        // Get requesting user info for context
        $requesting_user_id = $req['user_id'] ?? null;
        $requesting_user_info = null;
        if ($requesting_user_id) {
            $requesting_user_info = supabaseSelect('users', 'fname,lname', ['user_id' => $requesting_user_id], null, 1);
        }
        
        $child_name = trim(($child['child_fname'] ?? '') . ' ' . ($child['child_lname'] ?? ''));
        
        // Log approval activity
        supabaseLogActivity(
            $approver_id,
            $approver_type,
            'BABYCARD_DOC_APPROVED',
            $approver_name . ' approved Baby Card document request for ' . $child_name . ' (Request ID: ' . $request_id . ', Baby ID: ' . $req['baby_id'] . ')',
            $_SERVER['REMOTE_ADDR'] ?? null
        );
    } catch (Exception $e) {
        // Log error but don't fail the approval
        error_log('Failed to log Baby Card approval activity: ' . $e->getMessage());
    }
    
    echo json_encode(['status' => 'success', 'doc_url' => $doc_url]);
    exit();
    
} catch (Exception $e) {
    // Log full error details for debugging
    error_log('Baby Card approval error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    
    // Return detailed error message
    $errorMsg = $e->getMessage();
    if (empty($errorMsg)) {
        $errorMsg = 'Unknown error occurred. Check server logs for details.';
    }
    
    ob_end_clean(); // Clear any output buffer
    echo json_encode([
        'status' => 'error', 
        'message' => $errorMsg,
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    exit();
} catch (Error $e) {
    // Catch PHP 7+ errors (like missing methods, etc.)
    error_log('Baby Card approval PHP error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    
    ob_end_clean(); // Clear any output buffer
    echo json_encode([
        'status' => 'error', 
        'message' => 'PHP Error: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    exit();
}

// Clean output buffer if we reach here successfully
ob_end_flush();
?>

