<?php
include_once "../../../database/SupabaseConfig.php";
include_once "../../../database/DatabaseHelper.php";

try {
	$type = $_GET['type'] ?? '';
	$province = $_GET['province'] ?? '';
	$city_municipality = $_GET['city_municipality'] ?? '';
	$barangay = $_GET['barangay'] ?? '';

	$places = [];

	switch ($type) {
		case 'provinces':
			$rows = supabaseSelect('locations', 'province', [], 'province.asc');
			if ($rows) {
				$seen = [];
				foreach ($rows as $row) {
					$prov = $row['province'];
					if (!isset($seen[$prov])) {
						$places[] = array('province' => $prov);
						$seen[$prov] = true;
					}
				}
			}
			break;

		case 'cities':
			if (empty($province)) {
				throw new Exception("Province is required");
			}
			$rows = supabaseSelect('locations', 'city_municipality', ['province' => $province], 'city_municipality.asc');
			if ($rows) {
				$seen = [];
				foreach ($rows as $row) {
					$city = $row['city_municipality'];
					if (!isset($seen[$city])) {
						$places[] = array('city_municipality' => $city);
						$seen[$city] = true;
					}
				}
			}
			break;

		case 'barangays':
			if (empty($province) || empty($city_municipality)) {
				throw new Exception("Province and city/municipality are required");
			}
			$rows = supabaseSelect('locations', 'barangay', ['province' => $province, 'city_municipality' => $city_municipality], 'barangay.asc');
			if ($rows) {
				$seen = [];
				foreach ($rows as $row) {
					$brgy = $row['barangay'];
					if (!isset($seen[$brgy])) {
						$places[] = array('barangay' => $brgy);
						$seen[$brgy] = true;
					}
				}
			}
			break;

		case 'puroks':
			if (empty($province) || empty($city_municipality) || empty($barangay)) {
				throw new Exception("Province, city/municipality, and barangay are required");
			}
			$rows = supabaseSelect('locations', 'purok', [
				'province' => $province,
				'city_municipality' => $city_municipality,
				'barangay' => $barangay
			], 'purok.asc');
			if ($rows) {
				$seen = [];
				foreach ($rows as $row) {
					$purok = $row['purok'];
					if (!isset($seen[$purok])) {
						$places[] = array('purok' => $purok);
						$seen[$purok] = true;
					}
				}
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


