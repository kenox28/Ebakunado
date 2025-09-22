<?php
include "../../../database/SupabaseConfig.php";
include "../../../database/DatabaseHelper.php";

$logs = supabaseSelect('activity_logs', '*', [], 'created_at.desc');

$data = array();
if ($logs) {
	foreach ($logs as $row) {
		$data[] = $row;
	}
}
echo json_encode($data);

?>


