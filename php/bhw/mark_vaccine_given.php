<?php
session_start();
include '../../database/Database.php';
header('Content-Type: application/json');

if ($connect->connect_error) { echo json_encode(['status'=>'error','message'=>'DB error']); exit(); }
if (!isset($_SESSION['bhw_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$record_id = $_POST['record_id'] ?? '';
$date_given = $_POST['date_given'] ?? '';
$weight = $_POST['weight'] ?? null;
$height = $_POST['height'] ?? null;
$temperature = $_POST['temperature'] ?? null;

if ($record_id === '' || $date_given === '') { echo json_encode(['status'=>'error','message'=>'Missing fields']); exit(); }

$sql = "UPDATE immunization_records SET date_given = ?, status = 'completed', weight = ?, height = ?, temperature = ? WHERE id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param('sdddi', $date_given, $weight, $height, $temperature, $record_id);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Updated' : 'Update failed']);
