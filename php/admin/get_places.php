<?php
include_once "../../database/Database.php";

try {
    if (!$connect) {
        throw new Exception("Database connection failed");
    }

    $type = $_GET['type'] ?? '';
    $province = $_GET['province'] ?? '';
    $city_municipality = $_GET['city_municipality'] ?? '';
    $barangay = $_GET['barangay'] ?? '';

    $places = [];

    switch ($type) {
        case 'provinces':
            $query = "SELECT DISTINCT province FROM locations ORDER BY province";
            $result = mysqli_query($connect, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                $places[] = array('province' => $row['province']);
            }
            break;

        case 'cities':
            if (empty($province)) {
                throw new Exception("Province is required");
            }
            $query = "SELECT DISTINCT city_municipality FROM locations WHERE province = ? ORDER BY city_municipality";
            $stmt = $connect->prepare($query);
            $stmt->bind_param("s", $province);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $places[] = array('city_municipality' => $row['city_municipality']);
            }
            break;

        case 'barangays':
            if (empty($province) || empty($city_municipality)) {
                throw new Exception("Province and city/municipality are required");
            }
            $query = "SELECT DISTINCT barangay FROM locations WHERE province = ? AND city_municipality = ? ORDER BY barangay";
            $stmt = $connect->prepare($query);
            $stmt->bind_param("ss", $province, $city_municipality);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $places[] = array('barangay' => $row['barangay']);
            }
            break;

        case 'puroks':
            if (empty($province) || empty($city_municipality) || empty($barangay)) {
                throw new Exception("Province, city/municipality, and barangay are required");
            }
            $query = "SELECT DISTINCT purok FROM locations WHERE province = ? AND city_municipality = ? AND barangay = ? ORDER BY purok";
            $stmt = $connect->prepare($query);
            $stmt->bind_param("sss", $province, $city_municipality, $barangay);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $places[] = array('purok' => $row['purok']);
            }
            break;

        default:
            throw new Exception("Invalid type parameter");
    }

    echo json_encode($places);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
