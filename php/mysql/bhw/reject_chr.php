<?php

session_start();
include '../../../database/Database.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($connect->connect_error) {
    die('Connection Failed: ' . $connect->connect_error);
}

header('Content-Type: application/json');

$baby_id = $_POST['baby_id'] ?? '';

$stmt = $connect->prepare("UPDATE Child_Health_Records SET status = 'rejected' WHERE baby_id = ?");
$stmt->bind_param('s', $baby_id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['status' => 'success', 'message' => 'Record rejected']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Record not rejected']);
}

$connect->close();
?>