<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../shared/CHRTemplateGenerator.php';

use Dompdf\Dompdf;
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
    
    // Get child data
    $childRows = supabaseSelect('child_health_records', '*', ['baby_id' => $req['baby_id']], 'date_created.desc', 1);
    if (!$childRows || count($childRows) === 0) {
        throw new Exception('Child not found');
    }
    $child = $childRows[0];
    
    // Get immunization records
    $immunizationRows = supabaseSelect('immunization_records', '*', ['baby_id' => $req['baby_id']], 'date_given.asc');
    $immunizationData = $immunizationRows ?: [];
    
    // Get mother's TD data
    $tdRows = supabaseSelect('mother_tetanus_doses', '*', ['user_id' => $child['user_id']], null, 1);
    $tdData = $tdRows && count($tdRows) > 0 ? $tdRows[0] : [];
    
    // Pull philhealth and nhts from users
    $parentPhilhealth = '';
    $parentNhts = '';
    $parentPhone = '';
    if (!empty($child['user_id'])) {
        $u = supabaseSelect('users', 'phone_number,philhealth_no,nhts', ['user_id' => $child['user_id']], null, 1);
        if ($u && count($u) > 0) { 
            $parentPhone = $u[0]['phone_number'] ?? ''; 
            $parentPhilhealth = $u[0]['philhealth_no'] ?? ''; 
            $parentNhts = $u[0]['nhts'] ?? ''; 
        }
    }

    // Prepare child data for template
    $childData = [
        'name' => trim(($child['child_fname'] ?? '') . ' ' . ($child['child_lname'] ?? '')),
        'child_gender' => $child['child_gender'] ?? '',
        'gender' => $child['child_gender'] ?? '',
        'child_birth_date' => $child['child_birth_date'] ?? '',
        'place_of_birth' => $child['place_of_birth'] ?? '',
        'birth_weight' => $child['birth_weight'] ?? '',
        'birth_height' => $child['birth_height'] ?? '',
        'father_name' => $child['father_name'] ?? '',
        'mother_name' => $child['mother_name'] ?? '',
        'address' => $child['address'] ?? '',
        'lpm' => $child['lpm'] ?? '',
        'allergies' => $child['allergies'] ?? '',
        'delivery_type' => $child['delivery_type'] ?? '',
        'birth_order' => $child['birth_order'] ?? '',
        'birth_attendant' => $child['birth_attendant'] ?? '',
        'family_number' => $parentPhone,
        'philhealth' => $parentPhilhealth,
        'nhts' => $parentNhts,
        'non_nhts' => '',
        'family_planning' => $child['family_planning'] ?? '',
        'blood_type' => $child['blood_type'] ?? '',
        'nbs_date' => '',
        'nbs_place' => '',
        'birth_attendant_other' => '',
        // Feeding data
        'exclusive_breastfeeding_1mo' => $child['exclusive_breastfeeding_1mo'] ?? false,
        'exclusive_breastfeeding_2mo' => $child['exclusive_breastfeeding_2mo'] ?? false,
        'exclusive_breastfeeding_3mo' => $child['exclusive_breastfeeding_3mo'] ?? false,
        'exclusive_breastfeeding_4mo' => $child['exclusive_breastfeeding_4mo'] ?? false,
        'exclusive_breastfeeding_5mo' => $child['exclusive_breastfeeding_5mo'] ?? false,
        'exclusive_breastfeeding_6mo' => $child['exclusive_breastfeeding_6mo'] ?? false,
        'exclusive_breastfeeding_7mo' => false, // Not in current schema
        'exclusive_breastfeeding_8mo' => false, // Not in current schema
        'complementary_feeding_6mo' => $child['complementary_feeding_6mo'] ?? '',
        'complementary_feeding_7mo' => $child['complementary_feeding_7mo'] ?? '',
        'complementary_feeding_8mo' => $child['complementary_feeding_8mo'] ?? '',
    ];
    
    // Generate HTML using the template generator
    $templateGenerator = new CHRTemplateGenerator($childData, $immunizationData, [], $tdData);
    $html = $templateGenerator->generateHTML();
    
    // Generate PDF using DOMPDF with proper settings
    $dompdf = new Dompdf();
    $dompdf->set_paper([0, 0, 612, 936], 'portrait');
    $dompdf->load_html($html);
    $dompdf->render();
    
    $pdfOutput = $dompdf->output();
    if (empty($pdfOutput)) {
        throw new Exception('PDF generation failed - empty output');
    }
    
    $tmp = tempnam(sys_get_temp_dir(), 'chr_') . '.pdf';
    $written = file_put_contents($tmp, $pdfOutput);
    if ($written === false) {
        throw new Exception('Failed to write PDF to temp file');
    }
    
    if (!file_exists($tmp) || filesize($tmp) === 0) {
        throw new Exception('Temp file not created or empty');
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
    
    // Upload generated PDF to Cloudinary (as raw file)
    $uploadApi = new UploadApi();
    $publicId = 'chr_' . $req['baby_id'] . '_' . time();
    $result = $uploadApi->upload($tmp, [
        'public_id' => $publicId,
        'folder' => 'ebakunado/chr_docs',
        'resource_type' => 'image'
    ]);
    if (!$result || empty($result['secure_url'])) {
        throw new Exception('Cloudinary upload failed');
    }
    $doc_url = $result['secure_url'];
    
    @unlink($tmp);
    
    // Update database
    supabaseUpdate('chrdocrequest', [
        'status' => 'approved',
        'doc_url' => $doc_url,
        'approved_at' => date('Y-m-d H:i:s')
    ], ['id' => $request_id]);
    
    // Log activity: BHW/Midwife approved CHR document request
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
        $doc_type_label = ucfirst($req['request_type'] ?? '') . ' Copy';
        
        // Log approval activity
        supabaseLogActivity(
            $approver_id,
            $approver_type,
            'CHR_DOC_APPROVED',
            $approver_name . ' approved ' . $doc_type_label . ' CHR document request for ' . $child_name . ' (Request ID: ' . $request_id . ', Baby ID: ' . $req['baby_id'] . ')',
            $_SERVER['REMOTE_ADDR'] ?? null
        );
    } catch (Exception $e) {
        // Log error but don't fail the approval
        error_log('Failed to log CHR document approval activity: ' . $e->getMessage());
    }
    
    echo json_encode(['status' => 'success', 'doc_url' => $doc_url]);
    exit();
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit();
}
?>
