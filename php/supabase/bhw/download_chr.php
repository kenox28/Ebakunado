<?php
/**
 * BHW/Midwife: Generate and download CHR (Child Health Record) PDF on demand (no approval required).
 * - Validates BHW/Midwife session
 * - Loads child record + immunization records from Supabase
 * - Renders CHR HTML via CHRTemplateGenerator
 * - Generates PDF using Dompdf and streams it as attachment: CHR_<ChildName>.pdf
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';
require_once __DIR__ . '/../shared/CHRTemplateGenerator.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: application/pdf');

// Auth: allow BHW or Midwife
$bhwId = $_SESSION['bhw_id'] ?? null;
$midwifeId = $_SESSION['midwife_id'] ?? null;
if (!$bhwId && !$midwifeId) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

// Get baby_id
$babyId = $_GET['baby_id'] ?? $_POST['baby_id'] ?? '';
if (!$babyId) {
    http_response_code(400);
    echo 'Missing baby_id';
    exit;
}

try {
    // Fetch child record
    $childRows = supabaseSelect('child_health_records', '*', ['baby_id' => $babyId], null, 1);
    if (!$childRows || count($childRows) === 0) {
        http_response_code(404);
        echo 'Child record not found';
        exit;
    }
    $child = $childRows[0];

    // Fetch family_number, philhealth_no, and nhts from users table
    $family_number = '';
    $philhealth_no = '';
    $nhts = '';
    if (!empty($child['user_id'])) {
        $userRows = supabaseSelect('users', 'family_number,philhealth_no,nhts', ['user_id' => $child['user_id']], null, 1);
        if ($userRows && count($userRows) > 0) {
            $family_number = $userRows[0]['family_number'] ?? '';
            $philhealth_no = $userRows[0]['philhealth_no'] ?? '';
            $nhts = $userRows[0]['nhts'] ?? '';
        }
    }
    $child['family_number'] = $family_number;
    $child['philhealth_no'] = $philhealth_no;
    $child['nhts'] = $nhts;

    // Fetch immunization records (scheduled + taken) - include all relevant fields
    $immRows = supabaseSelect('immunization_records', '*', ['baby_id' => $babyId], 'schedule_date.asc');
    $immRows = $immRows ?: [];

    // Fetch Mother's TD doses from mother_tetanus_doses table using user_id
    $tdData = [
        'dose1_date' => '',
        'dose2_date' => '',
        'dose3_date' => '',
        'dose4_date' => '',
        'dose5_date' => '',
    ];
    if (!empty($child['user_id'])) {
        $tdRows = supabaseSelect('mother_tetanus_doses', 'dose1_date,dose2_date,dose3_date,dose4_date,dose5_date', ['user_id' => $child['user_id']], null, 1);
        if ($tdRows && count($tdRows) > 0) {
            $tdData = [
                'dose1_date' => $tdRows[0]['dose1_date'] ?? '',
                'dose2_date' => $tdRows[0]['dose2_date'] ?? '',
                'dose3_date' => $tdRows[0]['dose3_date'] ?? '',
                'dose4_date' => $tdRows[0]['dose4_date'] ?? '',
                'dose5_date' => $tdRows[0]['dose5_date'] ?? '',
            ];
        }
    }

    // Prepare child summary for CHR header (align with CHRTemplateGenerator usage)
    $childSummary = $child;
    $childSummary['name'] = trim(($child['child_fname'] ?? '') . ' ' . ($child['child_lname'] ?? ''));

    // Generate HTML via CHRTemplateGenerator
    $generator = new CHRTemplateGenerator($childSummary, $immRows, /* feedingData */ [], $tdData);
    $html = $generator->generateHTML();

    // Render to PDF using Dompdf
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    // CHR template uses 8.5in x 13in (Legal) portrait
    $dompdf->setPaper('legal', 'portrait');
    $dompdf->render();

    $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '', trim(($child['child_fname'] ?? '') . ($child['child_lname'] ?? '')));
    $fileName = 'CHR_' . ($safeName !== '' ? $safeName : $babyId) . '.pdf';

    // Stream as file download
    $dompdf->stream($fileName, ['Attachment' => true]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Failed to generate CHR: ' . $e->getMessage();
    exit;
}


