<?php
include "../../database/Database.php";

$sql = "SELECT * FROM users";
$result = $connect->query($sql);

$data = array();
for($i = 0; $i < $result->num_rows; $i++) {
    $data[] = $result->fetch_assoc();
}
echo json_encode($data);
?>