<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

/**
 * ==================================================================================
 * API ENDPOINT: get_child_details.php
 * PURPOSE: Returns complete child health record details for Child Health Record page
 * USED BY: Flutter Mobile App - Child Health Record Screen
 * ==================================================================================
 * 
 * ENDPOINT URL:
 * POST /ebakunado/php/supabase/users/get_child_details.php
 * 
 * REQUEST METHOD:
 * POST
 * 
 * REQUEST PARAMETERS:
 * -------------------
 * FormData Parameter: 'baby_id' (required)
 * Example: baby_id=BABY75785
 * 
 * REQUEST EXAMPLE:
 * -----------------
 * FormData:
 *   baby_id: BABY75785
 * 
 * RESPONSE FORMAT:
 * ----------------
 * {
 *   "status": "success",
 *   "data": [
 *     {
 *       "id": "uuid",
 *       "baby_id": "BABY75785",
 *       "user_id": "user123",
 *       "name": "John Doe",
 *       "child_fname": "John",
 *       "child_lname": "Doe",
 *       "child_gender": "Male",
 *       "child_birth_date": "2023-01-15",
 *       "place_of_birth": "City Health Center",
 *       "mother_name": "Jane Doe",
 *       "father_name": "John Doe Sr.",
 *       "address": "123 Main St, Ormoc City",
 *       "birth_weight": "3.5",
 *       "birth_height": "50",
 *       "birth_attendant": "Dr. Smith",
 *       "delivery_type": "Normal",
 *       "birth_order": "1",
 *       "family_number": "+639123456789",
 *       "philhealth_no": "PH123456",
 *       "nhts": "NHTS123",
 *       "age": 1,
 *       "weeks_old": 52.3,
 *       "status": "accepted",
 *       "qr_code": "https://res.cloudinary.com/demo/image/upload/v123/ebakunado/qr_codes/baby_BABY75785.png",
 *       "blood_type": "O+",
 *       "allergies": "None",
 *       "lpm": "2022-12-15",
 *       "family_planning": "NFP",
 *       "exclusive_breastfeeding_1mo": true,
 *       "exclusive_breastfeeding_2mo": true,
 *       "exclusive_breastfeeding_3mo": true,
 *       "exclusive_breastfeeding_4mo": true,
 *       "exclusive_breastfeeding_5mo": true,
 *       "exclusive_breastfeeding_6mo": true,
 *       "complementary_feeding_6mo": "Rice porridge",
 *       "complementary_feeding_7mo": "Rice, vegetables",
 *       "complementary_feeding_8mo": "Rice, vegetables, fruits",
 *       "mother_td_dose1_date": "2022-06-15",
 *       "mother_td_dose2_date": "2022-07-15",
 *       "mother_td_dose3_date": "2022-08-15",
 *       "mother_td_dose4_date": "",
 *       "mother_td_dose5_date": ""
 *     }
 *   ]
 * }
 * 
 * FLUTTER INTEGRATION:
 * ---------------------
 * 1. Create a POST request with baby_id in FormData
 * 2. Parse the JSON response
 * 3. Extract the first item from the 'data' array
 * 4. Display all fields in your UI
 * 
 * Example Flutter Code:
 * ---------------------
 * 
 * // Define Model
 * class ChildHealthRecord {
 *   final String babyId;
 *   final String name;
 *   final String gender;
 *   final String birthDate;
 *   // ... all other fields
 * }
 * 
 * // API Call
 * Future<ChildHealthRecord> getChildDetails(String babyId) async {
 *   final formData = FormData.fromMap({'baby_id': babyId});
 *   final response = await dio.post(
 *     '$baseUrl/php/supabase/users/get_child_details.php',
 *     data: formData,
 *   );
 *   
 *   if (response.data['status'] == 'success') {
 *     final data = response.data['data'][0];
 *     return ChildHealthRecord.fromJson(data);
 *   }
 *   throw Exception('Failed to load child details');
 * }
 * 
 * UI LAYOUT (Based on web version):
 * -----------------------------------
 * The Child Health Record should display:
 * 
 * 1. HEADER SECTION:
 *    - Title: "CHILD HEALTH RECORD"
 *    - Subtitle: "City Health Department, Ormoc City"
 * 
 * 2. CHILD PROFILE SECTION (2 columns):
 *    Left Column:
 *    - Name of Child
 *    - Gender
 *    - Date of Birth
 *    - Place of Birth
 *    - Birth Weight
 *    - Birth Length
 *    - Address
 *    - Allergies
 *    - Blood Type
 *    
 *    Right Column:
 *    - Family Number
 *    - LMP
 *    - PhilHealth No.
 *    - NHTS
 *    - Father's Name
 *    - Mother's Name
 *    - Family Planning
 * 
 * 3. CHILD HISTORY SECTION (2 columns):
 *    Left Column:
 *    - Date of Newborn Screening
 *    - Type of Delivery
 *    - Birth Order
 *    
 *    Right Column:
 *    - Place of Newborn Screening
 *    - Attended by
 * 
 * 4. FEEDING SECTION (Boxed/Highlighted):
 *    - Exclusive Breastfeeding (6 months):
 *      - 1st mo: ✓ or empty
 *      - 2nd mo: ✓ or empty
 *      - 3rd mo: ✓ or empty
 *      - 4th mo: ✓ or empty
 *      - 5th mo: ✓ or empty
 *      - 6th mo: ✓ or empty
 *    
 *    - Complementary Feeding:
 *      - 6th mo food: text
 *      - 7th mo food: text
 *      - 8th mo food: text
 * 
 * 5. MOTHER'S TD STATUS SECTION (Boxed/Highlighted):
 *    Display all 5 doses with dates:
 *    - TD 1st dose: MM/DD/YY
 *    - TD 2nd dose: MM/DD/YY
 *    - TD 3rd dose: MM/DD/YY
 *    - TD 4th dose: MM/DD/YY
 *    - TD 5th dose: MM/DD/YY
 *    (Use empty string if not available)
 * 
 * 6. VACCINATION LEDGER TABLE:
 *    Fetch from: /ebakunado/php/supabase/users/get_immunization_schedule.php
 *    Display only TAKEN vaccinations in a table with columns:
 *    - Date (format: MM/DD/YY)
 *    - Purpose (vaccine name)
 *    - HT (height)
 *    - WT (weight)
 *    - ME/AC (empty for now)
 *    - STATUS (Taken/Completed)
 *    - Condition of Baby (empty for now)
 *    - Advice Given (empty for now)
 *    - Next Sched Date (earliest upcoming vaccination)
 *    - Remarks (empty for now)
 * 
 * DATE FORMATTING:
 * ----------------
 * - Display dates in format: MM/DD/YY (e.g., "10/7/25")
 * - Use 2-digit year, no leading zeros for month/day
 * - Empty dates should show as blank
 * 
 * BOOLEAN FIELDS:
 * ---------------
 * - Exclusive breastfeeding fields are boolean
 * - Display as ✓ (checkmark) if true, blank if false
 * 
 * NOTE:
 * -----
 * This endpoint returns ALL fields needed for the complete Child Health Record.
 * The web version uses this same data to render the CHR document.
 * 
 * ==================================================================================
 */

$baby_id = $_POST['baby_id'];
$columns = 'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,place_of_birth,mother_name,father_name,address,birth_weight,birth_height,birth_attendant,babys_card,delivery_type,birth_order,date_created:date_created,date_updated:date_updated,status,qr_code,exclusive_breastfeeding_1mo,exclusive_breastfeeding_2mo,exclusive_breastfeeding_3mo,exclusive_breastfeeding_4mo,exclusive_breastfeeding_5mo,exclusive_breastfeeding_6mo,complementary_feeding_6mo,complementary_feeding_7mo,complementary_feeding_8mo,lpm,allergies,blood_type,family_planning';
$rows = supabaseSelect('child_health_records', $columns, ['baby_id' => $baby_id], 'date_created.desc');
$child_records = [];
if ($rows && count($rows) > 0) {
    foreach ($rows as $child) {
    $birth_date = new DateTime($child['child_birth_date']);
    $current_date = new DateTime();
    $weeks_old = $current_date->diff($birth_date)->days / 7;

    $age = $current_date->diff($birth_date)->y;

    // Resolve parent's phone and fetch TD using string user_id
    $family_number = '';
    $philhealth_no = '';
    $nhts = '';
    if (!empty($child['user_id'])) {
        $urows = supabaseSelect('users', 'phone_number,philhealth_no,nhts', ['user_id' => $child['user_id']], null, 1);
        if ($urows && count($urows) > 0) {
            $family_number = $urows[0]['phone_number'] ?? '';
            $philhealth_no = $urows[0]['philhealth_no'] ?? '';
            $nhts = $urows[0]['nhts'] ?? '';
        }
    }

    // Fetch Mother's TD doses from mother_tetanus_doses (by string user_id)
    $dose1 = $dose2 = $dose3 = $dose4 = $dose5 = '';
    if (!empty($child['user_id'])) {
        $td = supabaseSelect('mother_tetanus_doses', 'dose1_date,dose2_date,dose3_date,dose4_date,dose5_date', ['user_id' => $child['user_id']], null, 1);
        if ($td && count($td) > 0) {
            $dose1 = $td[0]['dose1_date'] ?? '';
            $dose2 = $td[0]['dose2_date'] ?? '';
            $dose3 = $td[0]['dose3_date'] ?? '';
            $dose4 = $td[0]['dose4_date'] ?? '';
            $dose5 = $td[0]['dose5_date'] ?? '';
        }
    }


$child_records[] = [
    'id' => $child['id'],
    'baby_id' => $child['baby_id'],
    'user_id' => $child['user_id'] ?? '',
    'name' => $child['child_fname'] . ' ' . $child['child_lname'],
    'child_fname' => $child['child_fname'],
    'child_lname' => $child['child_lname'],
    'child_gender' => $child['child_gender'],
    'child_birth_date' => $child['child_birth_date'],
    'place_of_birth' => $child['place_of_birth'],
    'mother_name' => $child['mother_name'],
    'father_name' => $child['father_name'],
    'address' => $child['address'],
    'birth_weight' => $child['birth_weight'],
    'birth_height' => $child['birth_height'],
    'birth_attendant' => $child['birth_attendant'],
    'delivery_type' => $child['delivery_type'] ?? '',
    'birth_order' => $child['birth_order'] ?? '',
    'family_number' => $family_number,
    'philhealth_no' => $philhealth_no,
    'nhts' => $nhts,
    'age' => $age,
    'weeks_old' => round($weeks_old, 1),
    'status' => $child['status'],
    'qr_code' => $child['qr_code'],
    'blood_type' => $child['blood_type'] ?? '',
    'allergies' => $child['allergies'] ?? '',
    'lpm' => $child['lpm'] ?? '',
    'family_planning' => $child['family_planning'] ?? '',
    // Exclusive Breastfeeding
    'exclusive_breastfeeding_1mo' => $child['exclusive_breastfeeding_1mo'] ?? false,
    'exclusive_breastfeeding_2mo' => $child['exclusive_breastfeeding_2mo'] ?? false,
    'exclusive_breastfeeding_3mo' => $child['exclusive_breastfeeding_3mo'] ?? false,
    'exclusive_breastfeeding_4mo' => $child['exclusive_breastfeeding_4mo'] ?? false,
    'exclusive_breastfeeding_5mo' => $child['exclusive_breastfeeding_5mo'] ?? false,
    'exclusive_breastfeeding_6mo' => $child['exclusive_breastfeeding_6mo'] ?? false,
    // Complementary Feeding
    'complementary_feeding_6mo' => $child['complementary_feeding_6mo'] ?? '',
    'complementary_feeding_7mo' => $child['complementary_feeding_7mo'] ?? '',
    'complementary_feeding_8mo' => $child['complementary_feeding_8mo'] ?? '',
    // Mother's TD Status (from mother_tetanus_doses)
    'mother_td_dose1_date' => $dose1,
    'mother_td_dose2_date' => $dose2,
    'mother_td_dose3_date' => $dose3,
    'mother_td_dose4_date' => $dose4,
    'mother_td_dose5_date' => $dose5
];
}
}

echo json_encode(['status'=>'success','data'=>$child_records ?: []]);



?>