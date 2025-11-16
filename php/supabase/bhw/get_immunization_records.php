<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$baby_id = $_GET['baby_id'] ?? '';
if ($baby_id === '') { echo json_encode(['status'=>'error','message'=>'Missing baby_id']); exit(); }

$columns = 'id,baby_id,vaccine_name,dose_number,status,schedule_date,date_given,catch_up_date,weight,height,temperature,muac,remarks,created_at';
$rows = supabaseSelect('immunization_records', $columns, ['baby_id' => $baby_id], 'schedule_date.asc');

// Auto-mark missed: if schedule_date is past today and not completed
if ($rows && is_array($rows)) {
	$todayDt = new DateTime('now', new DateTimeZone('Asia/Manila'));
	$today = $todayDt->format('Y-m-d');
	// catch-up will be computed from schedule_date (not from today)
	foreach ($rows as &$r) {
		$status = strtolower($r['status'] ?? '');
		$dateGiven = $r['date_given'] ?? null;
		$sched = $r['schedule_date'] ?? ($r['catch_up_date'] ?? null);
		if (!empty($sched) && empty($dateGiven) && $status !== 'completed') {
			if ($sched < $today) {
				if ($status !== 'missed' || empty($r['catch_up_date'])) {
					try {
						$base = new DateTime($sched, new DateTimeZone('Asia/Manila'));
						$catchUpFromSched = $base->modify('+7 day')->format('Y-m-d');
						$upd = supabaseUpdate('immunization_records', ['status' => 'missed', 'catch_up_date' => $catchUpFromSched], ['id' => $r['id']]);
						if ($upd === false) { error_log('Failed to update missed status for record ID ' . $r['id']); }
						$r['status'] = 'missed';
						$r['catch_up_date'] = $catchUpFromSched;
					} catch (Exception $e) {
						error_log('Failed to compute catch-up for record ID ' . $r['id'] . ': ' . $e->getMessage());
					}
				}
			}
		}
	}
}

echo json_encode(['status'=>'success','data'=>$rows ?: []]);
?>


