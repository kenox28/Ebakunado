<?php
session_start();
header('Content-Type: application/json');

/**
 * ==================================================================================
 * API ENDPOINT: get_children_summary.php
 * PURPOSE: Returns children summary data with QR codes for user dashboard
 * USED BY: Flutter Mobile App - User Home Page
 * ==================================================================================
 * 
 * QR CODE STRUCTURE:
 * ------------------
 * The QR code is stored in Cloudinary and contains the baby_id as encoded data.
 * 
 * QR Code URL Format:
 * Example: https://res.cloudinary.com/YOUR_CLOUD_NAME/image/upload/v1234567890/ebakunado/qr_codes/baby_ABC123.png
 * 
 * Full Structure:
 * - Upload Folder: 'ebakunado/qr_codes'
 * - Public ID Format: 'baby_' + baby_id (sanitized)
 * - Image Size: 600x600 pixels
 * - Quality: High (ECC level H)
 * - Data Encoded: The baby_id value (e.g., "ABC123")
 * 
 * Cloudinary Public ID Generation:
 * - Original baby_id: "ABC-123"
 * - Sanitized: "ABC_123" (special chars replaced with underscores)
 * - Final public_id: "baby_ABC_123"
 * 
 * How to Use in Flutter:
 * -----------------------
 * 1. Extract the 'qr_code' field from each child item in the 'items' array
 * 2. Display it as an image: Image.network(child['qr_code'])
 * 3. When scanning, extract the baby_id from the QR code data
 * 4. Example Flutter code:
 * 
 *   Image.network(
 *     child['qr_code'] ?? '', // The Cloudinary URL
 *     width: 60,
 *     height: 60,
 *     fit: BoxFit.cover,
 *   )
 * 
 * QR Code Scanning:
 * ------------------
 * When a QR code is scanned, it will contain ONLY the baby_id string
 * (e.g., "ABC123"). Use this to identify which child's record to display.
 * 
 * Example Response with QR Code:
 * -------------------------------
 * {
 *   "status": "success",
 *   "data": {
 *     "upcoming_count": 3,
 *     "missed_count": 2,
 *     "items": [
 *       {
 *         "baby_id": "ABC123",
 *         "name": "John Doe",
 *         "qr_code": "https://res.cloudinary.com/demo/image/upload/v123/ebakunado/qr_codes/baby_ABC123.png",
 *         "upcoming_date": "2024-02-15",
 *         "upcoming_vaccine": "Pentavalent 1st Dose",
 *         "missed_count": 2,
 *         "closest_missed": {
 *           "vaccine_name": "BCG",
 *           "dose_number": 1,
 *           "schedule_date": "2024-01-15",
 *           "catch_up_date": "2024-01-22"
 *         }
 *       }
 *     ]
 *   }
 * }
 * 
 * NULL Handling:
 * --------------
 * - If a child doesn't have a QR code yet, 'qr_code' will be null
 * - Always check for null before displaying the QR image
 * - Example: if (child['qr_code'] != null) { display QR code }
 * 
 * ==================================================================================
 */

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

try {
    $user_id = $_SESSION['user_id'];
    $filter = $_GET['filter'] ?? null; // 'upcoming' | 'missed' | null

    // Fetch children for this user
    $child_columns = 'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,status,qr_code';
    $children = supabaseSelect('child_health_records', $child_columns, ['user_id' => $user_id]);

    $summary = [
        'upcoming_count' => 0,
        'missed_count' => 0,
        'items' => []
    ];

    if (!$children || count($children) === 0) {
        echo json_encode(['status'=>'success','data'=>$summary]);
        exit();
    }

    $baby_ids = array_column($children, 'baby_id');
    $imm_columns = 'baby_id,vaccine_name,dose_number,status,schedule_date,batch_schedule_date,catch_up_date,date_given';
    $immunizations = supabaseSelect('immunization_records', $imm_columns, ['baby_id' => $baby_ids]);

    // Group immunizations by baby
    $imm_by_baby = [];
    if ($immunizations) {
        foreach ($immunizations as $imm) {
            $imm_by_baby[$imm['baby_id']][] = $imm;
        }
    }

    $today = date('Y-m-d');

    foreach ($children as $child) {
        if ($child['status'] !== 'accepted') { continue; }
        $baby_id = $child['baby_id'];
        $imm_list = $imm_by_baby[$baby_id] ?? [];

        $missed = 0;
        $upcoming_date = null;
        $upcoming_name = null;
        $upcoming_guideline = null;
        $upcoming_batch = null;

        $closest_upcoming = null;
        $closest_upcoming_date = null;

        foreach ($imm_list as $imm) {
            $status = strtolower($imm['status'] ?? '');
            $guideline = $imm['schedule_date'] ?? null;
            $batch = $imm['batch_schedule_date'] ?? null;
            $catch_up = $imm['catch_up_date'] ?? null;
            $operational = $batch ?: $guideline;

            // Count missed: explicit missed or scheduled past operational date
            if ($status === 'missed') {
                $missed++;
            } elseif ($status === 'scheduled' && $operational && $operational < $today) {
                $missed++;
            }

            // Determine next upcoming (scheduled future operational date)
            if ($status === 'scheduled' && $operational && $operational >= $today) {
                if ($closest_upcoming_date === null || strcmp($operational, $closest_upcoming_date) < 0) {
                    $closest_upcoming_date = $operational;
                    $closest_upcoming = [
                        'vaccine_name' => $imm['vaccine_name'],
                        'dose_number' => $imm['dose_number'],
                        'guideline' => $guideline,
                        'batch' => $batch,
                        'operational' => $operational
                    ];
                }
            }

            // Consider catch-up schedules for upcoming view
            if ($status === 'missed' && $catch_up && $catch_up >= $today) {
                if ($closest_upcoming_date === null || strcmp($catch_up, $closest_upcoming_date) < 0) {
                    $closest_upcoming_date = $catch_up;
                    $closest_upcoming = [
                        'vaccine_name' => $imm['vaccine_name'],
                        'dose_number' => $imm['dose_number'],
                        'guideline' => $guideline,
                        'batch' => null,
                        'operational' => $catch_up,
                        'type' => 'catch_up'
                    ];
                }
            }
        }

        if ($closest_upcoming) {
            $upcoming_date = $closest_upcoming['operational'];
            $upcoming_name = $closest_upcoming['vaccine_name'];
            $upcoming_guideline = $closest_upcoming['guideline'] ?? null;
            $upcoming_batch = $closest_upcoming['batch'] ?? null;
        }

        if ($upcoming_date) { $summary['upcoming_count']++; }
        if ($missed > 0) { $summary['missed_count']++; }

        if ($filter) {
            $include = false;
            if ($filter === 'upcoming' && $upcoming_date) { $include = true; }
            if ($filter === 'missed' && $missed > 0) { $include = true; }

            if ($include) {
                // For missed items, get the closest missed immunization detail
                $closest_missed = null;
                $closest_date = null;
                if ($filter === 'missed') {
                    foreach ($imm_list as $imm) {
                        $status = strtolower($imm['status'] ?? '');
                        $guideline = $imm['schedule_date'] ?? null;
                        $batch = $imm['batch_schedule_date'] ?? null;
                        $catch_up = $imm['catch_up_date'] ?? null;
                        $operational = $batch ?: $guideline;

                        $isMissed = ($status === 'missed') ||
                                    ($status === 'scheduled' && $operational && $operational < $today);

                        if ($isMissed) {
                            $date_to_compare = $catch_up ?: $operational;
                            if ($date_to_compare && ($closest_date === null || $date_to_compare < $closest_date)) {
                                $closest_date = $date_to_compare;
                                $closest_missed = [
                                    'vaccine_name' => $imm['vaccine_name'],
                                    'dose_number' => $imm['dose_number'],
                                    'schedule_date' => $guideline,
                                    'batch_schedule_date' => $batch,
                                    'catch_up_date' => $catch_up,
                                    'status' => $imm['status']
                                ];
                            }
                        }
                    }
                }
                
                $summary['items'][] = [
                    'baby_id' => $baby_id,
                    'name' => trim(($child['child_fname'] ?? '') . ' ' . ($child['child_lname'] ?? '')),
                    'upcoming_date' => $upcoming_date,
                    'upcoming_guideline' => $upcoming_guideline,
                    'upcoming_batch' => $upcoming_batch,
                    'upcoming_vaccine' => $upcoming_name,
                    'missed_count' => $missed,
                    'closest_missed' => $closest_missed, // Add closest missed detail
                    'qr_code' => $child['qr_code'] ?? null // Add QR code
                ];
            }
        }
    }

    echo json_encode(['status'=>'success','data'=>$summary]);

} catch (Exception $e) {
    error_log('get_children_summary error: ' . $e->getMessage());
    echo json_encode(['status'=>'error','message'=>'Failed to load summary']);
}
?>


