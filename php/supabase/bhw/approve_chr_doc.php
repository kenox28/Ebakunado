<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

if (!isset($_SESSION['bhw_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$request_id = $_POST['request_id'] ?? '';
$request_type_override = strtolower(trim($_POST['request_type'] ?? ''));
if ($request_id === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing request_id']);
    exit();
}

// Load request row
$reqRows = supabaseSelect('chrdocrequest', '*', ['id' => $request_id], null, 1);
if (!$reqRows || count($reqRows) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Request not found']);
    exit();
}
$req = $reqRows[0];
$request_type = $request_type_override !== '' ? $request_type_override : strtolower((string)($req['request_type'] ?? ''));

// Fetch child details
$childRows = supabaseSelect('child_health_records', '*', ['baby_id' => $req['baby_id']], 'date_created.desc', 1);
if (!$childRows || count($childRows) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Child not found']);
    exit();
}
$child = $childRows[0];

// Build formatted DOCX similar to the provided sheet
$word = new PhpWord();
$styleTitle = ['bold' => true, 'size' => 12];
$styleSubTitle = ['bold' => true, 'size' => 10];
$styleLabel = ['bold' => true, 'size' => 9];
$styleText = ['size' => 9];
$tableStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];

$word->addParagraphStyle('center', ['alignment' => 'center']);
$word->addTableStyle('grid', $tableStyle);

$section = $word->addSection([ 
    'pageSizeW' => 12240, 
    'pageSizeH' => 15840, 
    'marginTop' => 360, 
    'marginBottom' => 360, 
    'marginLeft' => 360, 
    'marginRight' => 360 
]);
$title = 'CHILD HEALTH RECORD';
if ($request_type === 'transfer') { $title .= ' — Transfer Copy'; }
if ($request_type === 'school') { $title .= ' — School Copy'; }
$section->addText($title, $styleTitle, 'center');
$section->addText('City Health Department, Ormoc City', $styleText, 'center');
$section->addTextBreak(0.5);

// Helper to line field
$line = function($label, $value = '') use ($section, $styleLabel, $styleText) {
    $p = $section->addTextRun();
    $p->addText($label . ' ', $styleLabel);
    $p->addText($value ? $value : '______________________________', $styleText);
};

// Header two columns
$t = $section->addTable();
$t->addRow();
$c1 = $t->addCell(6000);
$c2 = $t->addCell(6000);

$left = $c1->addTextRun();
$left->addText('Name of Child: ', $styleLabel); $left->addText(($child['child_fname'] ?? '') . ' ' . ($child['child_lname'] ?? ''), $styleText);
$c1->addTextBreak(0);
$lineL = function($label, $val = '') use ($c1, $styleLabel, $styleText){ $pr = $c1->addTextRun(); $pr->addText($label.' ', $styleLabel); $pr->addText($val ? $val : '____________________', $styleText); };
$lineR = function($label, $val = '') use ($c2, $styleLabel, $styleText){ $pr = $c2->addTextRun(); $pr->addText($label.' ', $styleLabel); $pr->addText($val ? $val : '____________________', $styleText); };

$lineL('Gender:', $child['child_gender'] ?? '');
$lineL('Date of Birth:', $child['child_birth_date'] ?? '');
$lineL('Place of Birth:', $child['place_of_birth'] ?? '');
$lineL('Birth Weight:', $child['birth_weight'] ?? '');
$lineL('Birth Length:', $child['birth_height'] ?? '');
$lineL('Address:', $child['address'] ?? '');
$lineL('Allergies:', '');
$lineL('Blood Type:', '');

// Right column
$lineR('Family Number:', '');
$lineR('Philhealth No.:', '');
$lineR('NHTS:', '');
$lineR('Non-NHTS:', '');
$lineR("Father's Name:", $child['father_name'] ?? '');
$lineR("Mother's Name:", $child['mother_name'] ?? '');
$lineR('LMP:', '');
$lineR('Family Planning:', '');

$section->addTextBreak(0.3);
$section->addText('CHILD HISTORY', $styleSubTitle, 'center');

$line('Date of Newbornscreening:', '');
$line('Place of Newbornscreening:', '');
$line('Type of Delivery:', $child['delivery_type'] ?? '');
$line('Birth Order:', $child['birth_order'] ?? '');
$line('Attended by:', $child['birth_attendant'] ?? '');

$section->addTextBreak(0.3);

// Exclusive Breastfeeding, Complementary Feeding & TD Status Section
$feedingTitle = $section->addTextRun();
$feedingTitle->addText('Exclusive Breastfeeding:Complementary Feeding:', $styleSubTitle);
$feedingTitle->addText(' TD Status: (date pls.)', $styleSubTitle);

// Create a table for feeding and TD status
$feedingTable = $section->addTable();
$feedingTable->addRow();

// Left column for Exclusive Breastfeeding and Complementary Feeding
$feedingLeft = $feedingTable->addCell(6000);
$feedingRight = $feedingTable->addCell(6000);

// Exclusive Breastfeeding section
$feedingLeft->addText('Exclusive Breastfeeding:', $styleLabel);
$feedingLeft->addTextBreak(0.2);

// Helper function to add feeding checkbox
$addFeedingCheckbox = function($month, $value) use ($feedingLeft, $styleText) {
    $text = $feedingLeft->addTextRun();
    $text->addText($month . ': ', $styleText);
    if ($value) {
        $text->addText('✓', $styleText);
    } else {
        $text->addText('☐', $styleText);
    }
    $feedingLeft->addTextBreak(0.05);
};

$addFeedingCheckbox('1st mo', $child['exclusive_breastfeeding_1mo'] ?? false);
$addFeedingCheckbox('2nd mo', $child['exclusive_breastfeeding_2mo'] ?? false);
$addFeedingCheckbox('3rd mo', $child['exclusive_breastfeeding_3mo'] ?? false);
$addFeedingCheckbox('4th mo', $child['exclusive_breastfeeding_4mo'] ?? false);
$addFeedingCheckbox('5th mo', $child['exclusive_breastfeeding_5mo'] ?? false);
$addFeedingCheckbox('6th mo', $child['exclusive_breastfeeding_6mo'] ?? false);

// Complementary Feeding section
$feedingLeft->addTextBreak(0.3);
$feedingLeft->addText('Complementary Feeding:', $styleLabel);
$feedingLeft->addTextBreak(0.2);

$addComplementaryFeeding = function($month, $food) use ($feedingLeft, $styleText) {
    $text = $feedingLeft->addTextRun();
    $text->addText($month . ' food: ', $styleText);
    $text->addText($food ? $food : '____________________', $styleText);
    $feedingLeft->addTextBreak(0.05);
};

$addComplementaryFeeding('6th mo', $child['complementary_feeding_6mo'] ?? '');
$addComplementaryFeeding('7th mo', $child['complementary_feeding_7mo'] ?? '');
$addComplementaryFeeding('8th mo', $child['complementary_feeding_8mo'] ?? '');

// Right column for Mother's TD Status
$feedingRight->addText('Mother\'s TD (Tetanus-Diphtheria) Status:', $styleLabel);
$feedingRight->addTextBreak(0.2);

$addTDDate = function($dose, $date) use ($feedingRight, $styleText) {
    $text = $feedingRight->addTextRun();
    $text->addText('TD ' . $dose . ' dose: ', $styleText);
    $text->addText($date ? $date : '____________________', $styleText);
    $feedingRight->addTextBreak(0.05);
};

$addTDDate('1st', $child['mother_td_dose1_date'] ?? '');
$addTDDate('2nd', $child['mother_td_dose2_date'] ?? '');
$addTDDate('3rd', $child['mother_td_dose3_date'] ?? '');
$addTDDate('4th', $child['mother_td_dose4_date'] ?? '');
$addTDDate('5th', $child['mother_td_dose5_date'] ?? '');

$section->addTextBreak(0.3);
$section->addText('IMMUNIZATION RECORD (pls. put the date)', $styleSubTitle, 'center');

// Fetch immunization rows for vaccine mapping and ledger
$immRows = supabaseSelect('immunization_records', '*', ['baby_id' => $req['baby_id']], 'schedule_date.asc');
$immRows = $immRows ?: [];

// Helper to find taken date for a vaccine label (with mapping for database names)
$getDate = function($displayName) use ($immRows){
    // Map display names to database names
    $nameMapping = [
        'BCG' => 'BCG',
        'Hepa B within 24 hrs' => 'HEPAB1 (w/in 24 hrs)',
        'Hepa B more than 24 hrs' => 'HEPAB1 (More than 24hrs)',
        'Pentavalent 1st dose' => 'Pentavalent (DPT-HepB-Hib) - 1st',
        'Pentavalent 2nd dose' => 'Pentavalent (DPT-HepB-Hib) - 2nd',
        'Pentavalent 3rd dose' => 'Pentavalent (DPT-HepB-Hib) - 3rd',
        'bOPV 1st dose' => 'OPV - 1st',
        'bOPV 2nd dose' => 'OPV - 2nd',
        'bOPV 3rd dose' => 'OPV - 3rd',
        'PCV 1st dose' => 'PCV - 1st',
        'PCV 2nd dose' => 'PCV - 2nd',
        'PCV 3rd dose' => 'PCV - 3rd',
        'MMR 1st dose' => 'MCV1 (AMV)',
        'MMR 2nd dose' => 'MCV2 (MMR)',
        'Rota Virus Vaccine - 1st' => 'Rota Virus Vaccine - 1st',
        'Rota Virus Vaccine - 2nd' => 'Rota Virus Vaccine - 2nd',
        'IPV' => 'IPV',
        'FIC' => 'FIC',
        'CIC' => 'CIC'
    ];
    
    $dbName = $nameMapping[$displayName] ?? $displayName;
    
    foreach ($immRows as $r){
        if (strcasecmp((string)$r['vaccine_name'], (string)$dbName) === 0 && in_array($r['status'], ['taken','completed'])){
            return $r['date_given'] ?? $r['schedule_date'] ?? '';
        }
    }
    return '';
};

// Create immunization record table with two columns like the template
$immTable = $section->addTable();
$immTable->addRow();

$immLeft = $immTable->addCell(6000);
$immRight = $immTable->addCell(6000);

// Left column vaccines
$leftVaccines = [
    'BCG',
    'Hepa B within 24 hrs',
    'Pentavalent 1st dose',
    'bOPV 1st dose',
    'PCV 1st dose',
    'MMR 1st dose',
    'Other Vaccines'
];

foreach ($leftVaccines as $vaccine) {
    $text = $immLeft->addTextRun();
    $text->addText($vaccine . ': ', $styleText);
    $text->addText($getDate($vaccine) ? $getDate($vaccine) : '____________________', $styleText);
    $immLeft->addTextBreak(0.05);
}

// Right column vaccines
$rightVaccines = [
    'Hepa B more than 24 hrs',
    'Pentavalent 2nd dose',
    'Pentavalent 3rd dose',
    'bOPV 2nd dose',
    'bOPV 3rd dose',
    'IPV',
    'PCV 2nd dose',
    'PCV 3rd dose',
    'MMR 2nd dose',
    'Rota Virus Vaccine - 1st',
    'Rota Virus Vaccine - 2nd',
    'FIC',
    'CIC'
];

foreach ($rightVaccines as $vaccine) {
    $text = $immRight->addTextRun();
    $text->addText($vaccine . ': ', $styleText);
    $text->addText($getDate($vaccine) ? $getDate($vaccine) : '____________________', $styleText);
    $immRight->addTextBreak(0.05);
}

// Add Scar field at the bottom right
$immRight->addTextBreak(0.3);
$scarText = $immRight->addTextRun();
$scarText->addText('Scar: (yes/no) ', $styleText);
$scarText->addText('____________________', $styleText);

$section->addTextBreak(0.3);

// Ledger table
$table = $section->addTable('grid');
$table->addRow();
foreach (['Date','Purpose','HT','WT','MUAC','STATUS','Condition of Baby','Advice Given','Next Sched Date','Remarks'] as $h){
    $table->addCell()->addText($h, $styleLabel);
}

// Build canonical order and best taken mapping (using database names)
$canonical = [
    'BCG','HEPAB1 (w/in 24 hrs)','HEPAB1 (More than 24hrs)',
    'Pentavalent (DPT-HepB-Hib) - 1st','OPV - 1st','PCV - 1st','Rota Virus Vaccine - 1st',
    'Pentavalent (DPT-HepB-Hib) - 2nd','OPV - 2nd','PCV - 2nd','Rota Virus Vaccine - 2nd',
    'Pentavalent (DPT-HepB-Hib) - 3rd','OPV - 3rd','PCV - 3rd','MCV1 (AMV)','MCV2 (MMR)'
];

$bestByName = [];
foreach ($immRows as $r){
    if (!in_array($r['status'], ['taken','completed'])) continue;
    $name = (string)$r['vaccine_name'];
    if (!isset($bestByName[$name])){ $bestByName[$name] = $r; continue; }
    $cur = $bestByName[$name];
    $dNew = (string)($r['date_given'] ?? '');
    $dCur = (string)($cur['date_given'] ?? '');
    if ($dNew && (!$dCur || $dNew < $dCur)) $bestByName[$name] = $r;
}

// Compute next schedule helper
$nextAfter = function($when) use ($immRows){
    $best = '';
    foreach ($immRows as $r){
        if (in_array($r['status'], ['taken','completed'])) continue;
        $due = $r['catch_up_date'] ?? $r['schedule_date'] ?? '';
        if (!$due) continue;
        if ($when && $due > $when){ if ($best === '' || $due < $best) $best = $due; }
    }
    return $best;
};

foreach ($canonical as $name){
    if (!isset($bestByName[$name])) continue;
    $r = $bestByName[$name];
    $date = $r['date_given'] ?? $r['schedule_date'] ?? '';
    $ht = $r['height'] ?? $r['height_cm'] ?? '';
    $wt = $r['weight'] ?? $r['weight_kg'] ?? '';
    $next = $nextAfter($date);
    $table->addRow();
    $table->addCell()->addText($date ? $date : '', $styleText);
    $table->addCell()->addText($name, $styleText);
    $table->addCell()->addText($ht, $styleText);
    $table->addCell()->addText($wt, $styleText);
    $table->addCell()->addText('', $styleText);
    $table->addCell()->addText('Taken', $styleText);
    $table->addCell()->addText('', $styleText);
    $table->addCell()->addText('', $styleText);
    $table->addCell()->addText($next ? $next : '', $styleText);
    $table->addCell()->addText('', $styleText);
}

// Add blank rows to complete a full ledger like the paper form (e.g., 15 rows total for single page)
$minRows = 15;
$currentRows = count($table->getRows());
while ($currentRows < ($minRows + 1)) { // +1 because header row already added
    $table->addRow();
    for ($i = 0; $i < 10; $i++) { $table->addCell()->addText('', $styleText); }
    $currentRows++;
}

// Save to temp file
$tmp = tempnam(sys_get_temp_dir(), 'chr_') . '.docx';
IOFactory::createWriter($word, 'Word2007')->save($tmp);

// Upload to Cloudinary
$cloudinaryConfig = include __DIR__ . '/../../../assets/config/cloudinary.php';
Configuration::instance([
    'cloud' => [
        'cloud_name' => $cloudinaryConfig['cloud_name'],
        'api_key' => $cloudinaryConfig['api_key'],
        'api_secret' => $cloudinaryConfig['api_secret']
    ],
    'url' => ['secure' => $cloudinaryConfig['secure']]
]);

$uploadApi = new UploadApi();
$public_id = 'chr_docs/chr_' . $req['baby_id'] . '_' . time();
$result = $uploadApi->upload($tmp, [
    'public_id' => $public_id,
    'folder' => 'ebakunado/chr_docs',
    'resource_type' => 'raw'
]);
$doc_url = $result['secure_url'] ?? '';

// Update request status
$upd = supabaseUpdate('chrdocrequest', [
    'status' => 'approved',
    'doc_url' => $doc_url,
    'approved_at' => date('Y-m-d H:i:s')
], ['id' => $request_id]);

if ($upd === false) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update request status']);
    exit();
}

// If transfer request, mark child as transferred (hide from BHW active lists)
if ($request_type === 'transfer') {
    // Try to set status='transferred' and timestamp; optional columns may not exist, fail-soft
    $transferUpdate = [ 'status' => 'transferred' ];
    $transferUpdate['date_updated'] = date('Y-m-d H:i:s');
    // If your table supports transferred_at/destination, you can extend here
    @supabaseUpdate('child_health_records', $transferUpdate, ['baby_id' => $req['baby_id']]);
}

echo json_encode(['status' => 'success', 'doc_url' => $doc_url]);
exit();
?>


