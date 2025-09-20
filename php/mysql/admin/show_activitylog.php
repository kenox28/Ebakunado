<?php
include "../../../database/Database.php";

$sql = "SELECT * FROM activity_logs ORDER BY created_at DESC";
$result = $connect->query($sql);

$data = array();
for($i = 0; $i < $result->num_rows; $i++) {
    $data[] = $result->fetch_assoc();
}
echo json_encode($data);

?>

