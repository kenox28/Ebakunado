<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$record_id = $_POST['record_id'] ?? '';
$date_given = $_POST['date_given'] ?? '';
$weight = $_POST['weight'] ?? null;
$height = $_POST['height'] ?? null;
$temperature = $_POST['temperature'] ?? null;

if ($record_id === '' || $date_given === '') { echo json_encode(['status'=>'error','message'=>'Missing fields']); exit(); }

// Coerce numeric fields
$toNum = function($v) { if ($v === null || $v === '') return null; $x = str_replace(',', '.', trim((string)$v)); return is_numeric($x) ? (float)$x : null; };
$weight = $toNum($weight);
$height = $toNum($height);
$temperature = $toNum($temperature);

$ok = supabaseUpdate('immunization_records', [
    'date_given' => $date_given,
    'status' => 'completed',
    'weight' => $weight,
    'height' => $height,
    'temperature' => $temperature
], ['id' => intval($record_id)]);

echo json_encode(['status' => $ok !== false ? 'success' : 'error', 'message' => $ok !== false ? 'Updated' : 'Update failed']);
?>


