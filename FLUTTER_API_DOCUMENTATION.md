# Flutter API - Baby Card Download Endpoints

## Overview

**For Flutter users to download Baby Card (Immunization Card):**

1. Get approved requests (to get `baby_id`)
2. Get child details
3. Get immunization records
4. Get layout configuration
5. Generate Baby Card PDF client-side in Flutter
6. Download the generated PDF

---

## Required Endpoints for Baby Card

### 1. **Get Approved Requests**

Get list of approved requests to extract `baby_id`.

**Endpoint:** `GET /ebakunado/php/supabase/users/get_my_chr_requests.php`

**Authentication:** Session required (`user_id`)

**Response:**

```json
{
	"status": "success",
	"data": [
		{
			"id": 123,
			"baby_id": "baby456",
			"child_name": "Juan Dela Cruz",
			"status": "approved",
			"approved_at": "2025-01-15 14:30:00"
		}
	]
}
```

**Usage:**

- Extract `baby_id` from the approved request
- Use `baby_id` to fetch child details and immunization records

---

### 2. **Get Child Details**

Get child information for Baby Card.

**Endpoint:** `POST /ebakunado/php/supabase/users/get_child_details.php`

**Authentication:** Session required (`user_id`)

**Request Body (FormData):**

```
baby_id: string (required)
```

**Response:**

```json
{
	"status": "success",
	"data": [
		{
			"baby_id": "baby456",
			"child_fname": "Juan",
			"child_lname": "Dela Cruz",
			"child_birth_date": "2020-05-15",
			"child_gender": "M",
			"place_of_birth": "Linao Health Center",
			"birth_weight": "3.2",
			"birth_height": "50",
			"father_name": "Pedro Dela Cruz",
			"mother_name": "Maria Dela Cruz",
			"address": "123 Main St, Barangay Linao",
			"family_number": "09123456789"
		}
	]
}
```

---

### 3. **Get Immunization Records**

Get vaccination history for Baby Card.

**Endpoint:** `POST /ebakunado/php/supabase/users/get_my_immunization_records.php`

**Authentication:** Session required (`user_id`)

**Request Body (FormData):**

```
baby_id: string (required)
```

**Response:**

```json
{
	"status": "success",
	"data": [
		{
			"vaccine_name": "BCG",
			"dose_number": "1",
			"date_given": "2020-06-01",
			"status": "taken"
		},
		{
			"vaccine_name": "PENTAVALENT",
			"dose_number": "1",
			"date_given": "2020-06-15",
			"status": "taken"
		},
		{
			"vaccine_name": "HEPATITIS B",
			"dose_number": "1",
			"date_given": "2020-06-15",
			"status": "taken"
		}
	]
}
```

---

### 4. **Get Baby Card Layout Configuration**

Get layout positions and settings for generating the Baby Card.

**Endpoint:** `GET /ebakunado/php/supabase/users/get_babycard_layout.php`

**OR** load from JSON file:
**Endpoint:** `GET /ebakunado/assets/config/babycard_layout.json`

**Authentication:** None required (public)

**Response:**

```json
{
	"page": {
		"format": "a4",
		"orientation": "landscape",
		"unit": "mm",
		"_dimensions_mm": {
			"width": 297,
			"height": 210
		},
		"_dimensions_points": {
			"width": 841.89,
			"height": 595.28
		}
	},
	"fonts": {
		"details_pt": 12,
		"vaccines_pt": 11
	},
	"boxes": {
		"left_x_pct": 36.92,
		"right_x_pct": 74.12,
		"rows_y_pct": {
			"r1": 16.93,
			"r2": 20.92,
			"r3": 24.53,
			"r4": 28.02
		},
		"sex_m": {
			"x_pct": 91.51,
			"y_pct": 28.4
		},
		"sex_f": {
			"x_pct": 95.51,
			"y_pct": 28.15
		},
		"max_width_pct": 26
	},
	"vaccines": {
		"cols_x_pct": {
			"c1": 60.2,
			"c2": 67.46,
			"c3": 74.53
		},
		"rows_y_pct": {
			"BCG": 39.37,
			"HEPATITIS B": 44.1,
			"PENTAVALENT": 48.84,
			"OPV": 53.83,
			"PCV": 63.17,
			"MMR": 68.04
		}
	},
	"extras": {
		"health_center": {
			"text": "Linao Health Center",
			"x_pct": 5.96,
			"y_pct": 49.96,
			"max_width_pct": 22
		},
		"barangay": {
			"x_pct": 5.88,
			"y_pct": 60.43,
			"max_width_pct": 22
		},
		"family_no": {
			"x_pct": 5.71,
			"y_pct": 71.15,
			"max_width_pct": 22
		}
	},
	"background_path": "assets/images/babycard.jpg",
	"fallback_background_path": "assets/images/babycard.jpg"
}
```

---

## Flutter Implementation

### Step-by-Step Flow:

```dart
// 1. Get approved requests
final requestsResponse = await http.get(
  Uri.parse('$baseUrl/php/supabase/users/get_my_chr_requests.php'),
  headers: {'Cookie': sessionCookie},
);
final requests = jsonDecode(requestsResponse.body)['data'] as List;
final babyId = requests[0]['baby_id']; // Get baby_id from approved request

// 2. Get child details
final childResponse = await http.post(
  Uri.parse('$baseUrl/php/supabase/users/get_child_details.php'),
  body: {'baby_id': babyId},
  headers: {'Cookie': sessionCookie},
);
final childData = jsonDecode(childResponse.body)['data'][0];

// 3. Get immunization records
final immResponse = await http.post(
  Uri.parse('$baseUrl/php/supabase/users/get_my_immunization_records.php'),
  body: {'baby_id': babyId},
  headers: {'Cookie': sessionCookie},
);
final immunizations = jsonDecode(immResponse.body)['data'] as List;

// 4. Get layout configuration
final layoutResponse = await http.get(
  Uri.parse('$baseUrl/php/supabase/users/get_babycard_layout.php'),
);
final layoutConfig = jsonDecode(layoutResponse.body);

// 5. Generate Baby Card PDF in Flutter
// Use pdf package (e.g., pdf: ^3.10.0) to generate PDF
final pdfBytes = await generateBabyCardPdf(
  childData: childData,
  immunizations: immunizations,
  layoutConfig: layoutConfig,
);

// 6. Save/download PDF
await File('BabyCard_${childData["child_fname"]}_${childData["child_lname"]}.pdf')
    .writeAsBytes(pdfBytes);
```

---

## PDF Generation Details

### Page Dimensions:

- **Format:** A4 Landscape
- **Width:** 297mm (841.89 points)
- **Height:** 210mm (595.28 points)

### Coordinates:

- All `x_pct` and `y_pct` values are **percentages (0-100)**
- Convert to actual position:
  ```dart
  double xMm = (xPct / 100) * 297;  // In millimeters
  double yMm = (yPct / 100) * 210;  // In millimeters
  double xPt = (xPct / 100) * 841.89;  // In points
  double yPt = (yPct / 100) * 595.28;  // In points
  ```

### Fields to Fill:

**Left Column (at `left_x_pct`):**

- Row 1 (`r1`): Child Name (`child_fname + child_lname`)
- Row 2 (`r2`): Birth Date (`child_birth_date`)
- Row 3 (`r3`): Place of Birth (`place_of_birth`)
- Row 4 (`r4`): Address (`address`)

**Right Column (at `right_x_pct`):**

- Row 1 (`r1`): Mother Name (`mother_name`)
- Row 2 (`r2`): Father Name (`father_name`)
- Row 3 (`r3`): Birth Height (`birth_height`)
- Row 4 (`r4`, at `right_r4_x_pct`, `right_r4_y_pct`): Birth Weight (`birth_weight`)

**Sex Mark:**

- If `child_gender == "M"`: Mark 'X' at `sex_m.x_pct`, `sex_m.y_pct`
- If `child_gender == "F"`: Mark 'X' at `sex_f.x_pct`, `sex_f.y_pct`

**Extras:**

- Health Center: `extras.health_center.text` at `extras.health_center.x_pct`, `extras.health_center.y_pct`
- Barangay: Extract from `address` (last part after comma) at `extras.barangay.x_pct`, `extras.barangay.y_pct`
- Family No: `family_number` at `extras.family_no.x_pct`, `extras.family_no.y_pct`

**Vaccine Dates:**

- Match vaccine name to layout row:
  - BCG → `vaccines.rows_y_pct.BCG`
  - HEPATITIS B → `vaccines.rows_y_pct.HEPATITIS B`
  - PENTAVALENT → `vaccines.rows_y_pct.PENTAVALENT`
  - OPV → `vaccines.rows_y_pct.OPV`
  - PCV → `vaccines.rows_y_pct.PCV`
  - MMR → `vaccines.rows_y_pct.MMR`
- Column based on dose number:
  - Dose 1 → `vaccines.cols_x_pct.c1`
  - Dose 2 → `vaccines.cols_x_pct.c2`
  - Dose 3 → `vaccines.cols_x_pct.c3` (for PENTAVALENT, OPV, PCV)
- Format date as: `M/D/YY` (e.g., `6/1/20`)

---

## Vaccine Name Mapping

When matching immunization records to layout rows:

```dart
String? mapVaccineName(String vaccineName) {
  final name = vaccineName.toUpperCase();
  if (name.contains('BCG')) return 'BCG';
  if (name.contains('HEP')) return 'HEPATITIS B';
  if (name.contains('PENTA') || name.contains('HIB')) return 'PENTAVALENT';
  if (name.contains('OPV') || name.contains('ORAL POLIO')) return 'OPV';
  if (name.contains('PCV') || name.contains('PNEUMO')) return 'PCV';
  if (name.contains('MMR') || name.contains('MEASLES')) return 'MMR';
  return null;
}
```

---

## Summary

**For Flutter Baby Card Download:**

1. ✅ `GET /php/supabase/users/get_my_chr_requests.php` - Get approved requests
2. ✅ `POST /php/supabase/users/get_child_details.php` - Get child data
3. ✅ `POST /php/supabase/users/get_my_immunization_records.php` - Get vaccines
4. ✅ `GET /php/supabase/users/get_babycard_layout.php` - Get layout config
5. ✅ Generate PDF client-side using layout coordinates
6. ✅ Save/download the PDF file

**All endpoints require session authentication except the layout endpoint.**
