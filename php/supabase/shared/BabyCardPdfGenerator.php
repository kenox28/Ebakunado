<?php
/**
 * Baby Card PDF Generator
 * Replicates JavaScript renderBabyCardPdf() function using DOMPDF
 * Ensures 100% identical layout and positioning
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class BabyCardPdfGenerator {
    
    /**
     * Generate Baby Card PDF (replicates JavaScript renderBabyCardPdf)
     * 
     * @param array $child Child data from get_child_details.php
     * @param array $immunizations Immunization records from get_my_immunization_records.php
     * @return string PDF bytes
     */
    public static function generate($child, $immunizations) {
        // 1) Load layout JSON
        $layoutPath = __DIR__ . '/../../../assets/config/babycard_layout.json';
        $layout = null;
        if (file_exists($layoutPath)) {
            $layoutJson = file_get_contents($layoutPath);
            $layout = json_decode($layoutJson, true);
        }
        
        // 2) Resolve background image path and convert to base64
        $projectRoot = __DIR__ . '/../../../';
        $projectRoot = realpath($projectRoot);
        
        $bgPath = null;
        
        // Try layout background_path first
        if ($layout && isset($layout['background_path'])) {
            $relativePath = ltrim($layout['background_path'], '/');
            $testPath = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            if (file_exists($testPath)) {
                $bgPath = $testPath;
            }
        }
        
        // Try fallback background_path
        if (!$bgPath && $layout && isset($layout['fallback_background_path'])) {
            $relativePath = ltrim($layout['fallback_background_path'], '/');
            $testPath = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            if (file_exists($testPath)) {
                $bgPath = $testPath;
            }
        }
        
        // Try default path
        if (!$bgPath) {
            $testPath = $projectRoot . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'babycard.jpg';
            if (file_exists($testPath)) {
                $bgPath = $testPath;
            }
        }
        
        if (!$bgPath || !file_exists($bgPath)) {
            throw new Exception('Background image not found. Searched: ' . 
                ($projectRoot ? $projectRoot . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'babycard.jpg' : 'unknown') .
                '. File should be at: assets/images/babycard.jpg');
        }
        
        // Convert image to base64 for embedding in HTML
        $imageData = file_get_contents($bgPath);
        $base64Image = 'data:image/jpeg;base64,' . base64_encode($imageData);
        
        // 3) Get layout parameters
        $b = $layout && isset($layout['boxes']) ? $layout['boxes'] : [];
        $f = $layout && isset($layout['fonts']) ? $layout['fonts'] : [];
        $v = $layout && isset($layout['vaccines']) ? $layout['vaccines'] : [];
        $ex = $layout && isset($layout['extras']) ? $layout['extras'] : [];
        
        // 4) Prepare data
        $childName = trim(($child['child_fname'] ?? '') . ' ' . ($child['child_lname'] ?? ''));
        $birthDate = substr($child['child_birth_date'] ?? '', 0, 10);
        $placeOfBirth = htmlspecialchars($child['place_of_birth'] ?? '');
        $address = htmlspecialchars($child['address'] ?? '');
        $motherName = htmlspecialchars($child['mother_name'] ?? '');
        $fatherName = htmlspecialchars($child['father_name'] ?? '');
        $birthHeight = htmlspecialchars($child['birth_height'] ?? '');
        $birthWeight = htmlspecialchars($child['birth_weight'] ?? '');
        $gender = strtoupper($child['child_gender'] ?? '');
        $familyNumber = htmlspecialchars($child['family_number'] ?? '');
        
        // Note: Barangay will be set later after $ex is defined
        // (using static text from JSON if available, otherwise from address)
        
        // 5) Format vaccine dates
        $formatDateShort = function($iso) {
            if (empty($iso)) return '';
            try {
                $d = new DateTime($iso);
                $m = intval($d->format('n'));
                $day = intval($d->format('j'));
                $y = intval($d->format('y'));
                return sprintf('%d/%d/%02d', $m, $day, $y);
            } catch (Exception $e) {
                // Try parsing YYYY-MM-DD
                $p = explode('-', explode('T', strval($iso))[0]);
                if (count($p) === 3) {
                    $m = intval($p[1]);
                    $day = intval($p[2]);
                    $y = intval($p[0]) % 100;
                    return sprintf('%d/%d/%02d', $m, $day, $y);
                }
                return strval($iso);
            }
        };
        
        // Map immunizations to vaccine keys
        $vkey = function($name) {
            $n = strtoupper($name ?? '');
            if (strpos($n, 'BCG') !== false) return 'BCG';
            if (strpos($n, 'HEP') !== false) return 'HEPATITIS B';
            if (strpos($n, 'PENTA') !== false || strpos($n, 'HIB') !== false) return 'PENTAVALENT';
            if (strpos($n, 'OPV') !== false || strpos($n, 'ORAL POLIO') !== false) return 'OPV';
            if (strpos($n, 'PCV') !== false || strpos($n, 'PNEUMO') !== false) return 'PCV';
            if (strpos($n, 'MMR') !== false || strpos($n, 'MEASLES') !== false) return 'MMR';
            return null;
        };
        
        // Build vaccine date map
        $vaccineDates = [];
        foreach ($immunizations as $imm) {
            if (empty($imm['date_given'])) continue;
            $key = $vkey($imm['vaccine_name'] ?? '');
            if (!$key) continue;
            $dose = intval($imm['dose_number'] ?? 1);
            $dateStr = $formatDateShort($imm['date_given']);
            
            if (!isset($vaccineDates[$key])) {
                $vaccineDates[$key] = [];
            }
            $vaccineDates[$key][$dose] = $dateStr;
        }
        
        // 6) Get page dimensions from layout JSON (or use defaults)
        $pageConfig = $layout && isset($layout['page']) ? $layout['page'] : [];
        $pageDimensions = $pageConfig['_dimensions_mm'] ?? ['width' => 297, 'height' => 210];
        $pageWidth = floatval($pageDimensions['width'] ?? 297);
        $pageHeight = floatval($pageDimensions['height'] ?? 210);
        $pageOrientation = ($pageConfig['orientation'] ?? 'landscape') === 'landscape' ? 'landscape' : 'portrait';
        
        // 7) Build HTML with absolute positioning
        $rowsY = $b['rows_y_pct'] ?? [];
        $colsX = $v['cols_x_pct'] ?? [];
        $vaccinesY = $v['rows_y_pct'] ?? [];
        
        // Helper to generate positioned text element
        // The calibrator tool visualizes positions from top (0-100%)
        // Layout JSON stores these percentages from top
        // Both jsPDF and DOMPDF need to position text correctly
        // Since positions aren't perfect, let's try using y_pct directly first
        // and add a configurable offset if needed (can be added to layout JSON later)
        $textElement = function($text, $xpct, $ypct, $pt = null, $align = 'left', $maxWidthPct = null) use ($f, $pageHeight, $layout) {
            $size = $pt ? floatval($pt) : (floatval($f['details_pt'] ?? 12));
            $x = floatval($xpct);
            $y = floatval($ypct);
            
            // Check if layout has DOMPDF-specific offset adjustments
            $dompdfOffset = $layout && isset($layout['dompdf_offset']) ? $layout['dompdf_offset'] : null;
            $xOffset = 0;
            $yOffset = 0;
            if ($dompdfOffset) {
                if (isset($dompdfOffset['x_pct'])) {
                    $xOffset = floatval($dompdfOffset['x_pct']);
                }
                if (isset($dompdfOffset['y_pct'])) {
                    $yOffset = floatval($dompdfOffset['y_pct']);
                }
            }
            
            // Apply offsets for DOMPDF fine-tuning
            $xAdjusted = $x + $xOffset;
            $yAdjusted = $y + $yOffset;
            
            // Ensure non-negative and within bounds
            $xAdjusted = max(0, min(100, $xAdjusted));
            $yAdjusted = max(0, min(100, $yAdjusted));
            
            $maxW = $maxWidthPct ? 'max-width: ' . floatval($maxWidthPct) . '%; word-wrap: break-word; overflow-wrap: break-word;' : '';
            $textAlign = $align === 'center' ? 'center' : ($align === 'right' ? 'right' : 'left');
            $txt = htmlspecialchars($text ?? '');
            
            return sprintf(
                '<div style="position: absolute; left: %.2f%%; top: %.2f%%; font-size: %.1fpt; line-height: 1; color: #141e28; text-align: %s; %s; text-shadow: 0.5px 0.8px 0 white, -0.5px -0.8px 0 white, 0.5px -0.8px 0 white, -0.5px 0.8px 0 white; white-space: pre-wrap;">%s</div>',
                $xAdjusted, $yAdjusted, $size, $textAlign, $maxW, $txt
            );
        };
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: ' . $pageWidth . 'mm ' . $pageHeight . 'mm ' . $pageOrientation . ';
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            position: relative;
            width: ' . $pageWidth . 'mm;
            height: ' . $pageHeight . 'mm;
        }
        .bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        .content {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <img src="' . $base64Image . '" class="bg-image" />
    <div class="content">';
        
        // Child details - left column
        $html .= $textElement($childName, $b['left_x_pct'] ?? 36, $rowsY['r1'] ?? 21, $f['details_pt'] ?? 12, 'left', $b['max_width_pct'] ?? 26);
        $html .= $textElement($birthDate, $b['left_x_pct'] ?? 36, $rowsY['r2'] ?? 26, $f['details_pt'] ?? 12, 'left', $b['max_width_pct'] ?? 26);
        $html .= $textElement($placeOfBirth, $b['left_x_pct'] ?? 36, $rowsY['r3'] ?? 30, $f['details_pt'] ?? 12, 'left', $b['max_width_pct'] ?? 26);
        $html .= $textElement($address, $b['left_x_pct'] ?? 36, $rowsY['r4'] ?? 35, $f['details_pt'] ?? 12, 'left', $b['max_width_pct'] ?? 26);
        
        // Child details - right column
        $html .= $textElement($motherName, $b['right_x_pct'] ?? 77, $rowsY['r1'] ?? 21, $f['details_pt'] ?? 12, 'left', $b['max_width_pct'] ?? 26);
        $html .= $textElement($fatherName, $b['right_x_pct'] ?? 77, $rowsY['r2'] ?? 26, $f['details_pt'] ?? 12, 'left', $b['max_width_pct'] ?? 26);
        $html .= $textElement($birthHeight, $b['right_x_pct'] ?? 77, $rowsY['r3'] ?? 30, $f['details_pt'] ?? 12, 'left', $b['max_width_pct'] ?? 26);
        
        // Independent position for right row 4 (Birth Weight)
        $rightR4X = isset($b['right_r4_x_pct']) && is_numeric($b['right_r4_x_pct']) ? $b['right_r4_x_pct'] : ($b['right_x_pct'] ?? 77);
        $rightR4Y = isset($b['right_r4_y_pct']) && is_numeric($b['right_r4_y_pct']) ? $b['right_r4_y_pct'] : ($rowsY['r4'] ?? 35);
        $html .= $textElement($birthWeight, $rightR4X, $rightR4Y, $f['details_pt'] ?? 12, 'left', $b['max_width_pct'] ?? 26);
        
        // Sex mark X only
        if (strpos($gender, 'M') === 0 && isset($b['sex_m'])) {
            $html .= $textElement('X', $b['sex_m']['x_pct'], $b['sex_m']['y_pct'], $f['details_pt'] ?? 12, 'center');
        } elseif (strpos($gender, 'F') === 0 && isset($b['sex_f'])) {
            $html .= $textElement('X', $b['sex_f']['x_pct'], $b['sex_f']['y_pct'], $f['details_pt'] ?? 12, 'center');
        }
        
        // Extras: Health Center, Barangay, Family No
        if (isset($ex['health_center'])) {
            $hc = $ex['health_center'];
            $html .= $textElement($hc['text'] ?? 'Linao Health Center', $hc['x_pct'], $hc['y_pct'], $f['details_pt'] ?? 12, 'left', $hc['max_width_pct'] ?? 22);
        }
        if (isset($ex['barangay'])) {
            // Barangay: Use static text from JSON if available (e.g., "Linao"), otherwise extract from address
            $brgy = '';
            if (isset($ex['barangay']['text'])) {
                // Use static text from JSON
                $brgy = $ex['barangay']['text'];
            } elseif (!empty($address)) {
                // Fallback: extract from address
                $addrParts = explode(',', $address);
                $brgy = trim(end($addrParts));
            }
            $html .= $textElement($brgy, $ex['barangay']['x_pct'], $ex['barangay']['y_pct'], $f['details_pt'] ?? 12, 'left', $ex['barangay']['max_width_pct'] ?? 22);
        }
        if (isset($ex['family_no'])) {
            // Family number is dynamic - comes from child's family_number field
            $html .= $textElement($familyNumber, $ex['family_no']['x_pct'], $ex['family_no']['y_pct'], $f['details_pt'] ?? 12, 'left', $ex['family_no']['max_width_pct'] ?? 22);
        }
        
        // Vaccine dates (exact same logic as JavaScript)
        foreach ($vaccineDates as $key => $doses) {
            $y = $vaccinesY[$key] ?? ($vaccinesY['HEPATITIS B'] ?? 56.1);
            
            if ($key === 'MMR') {
                // MMR has 2 doses → place in c1 (dose 1) or c2 (dose 2)
                if (isset($doses[1])) {
                    $html .= $textElement($doses[1], $colsX['c1'] ?? 60.2, $y, $f['vaccines_pt'] ?? 11, 'center');
                }
                if (isset($doses[2])) {
                    $html .= $textElement($doses[2], $colsX['c2'] ?? 69.2, $y, $f['vaccines_pt'] ?? 11, 'center');
                }
            } elseif ($key === 'BCG' || $key === 'HEPATITIS B') {
                // Single-dose rows always use the first column
                if (isset($doses[1])) {
                    $html .= $textElement($doses[1], $colsX['c1'] ?? 60.2, $y, $f['vaccines_pt'] ?? 11, 'center');
                }
            } else {
                // PENTAVALENT / OPV / PCV → 3 doses map to c1, c2, c3
                if (isset($doses[1])) {
                    $html .= $textElement($doses[1], $colsX['c1'] ?? 60.2, $y, $f['vaccines_pt'] ?? 11, 'center');
                }
                if (isset($doses[2])) {
                    $html .= $textElement($doses[2], $colsX['c2'] ?? 69.2, $y, $f['vaccines_pt'] ?? 11, 'center');
                }
                if (isset($doses[3])) {
                    $html .= $textElement($doses[3], $colsX['c3'] ?? 78.2, $y, $f['vaccines_pt'] ?? 11, 'center');
                }
            }
        }
        
        $html .= '
    </div>
</body>
</html>';
        
        // 8) Generate PDF using DOMPDF with dimensions from layout JSON
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        
        // Use custom paper size from layout JSON (in mm, convert to points for DOMPDF)
        // 1mm = 2.83465 points
        $widthPt = $pageWidth * 2.83465;
        $heightPt = $pageHeight * 2.83465;
        $dompdf->setPaper([0, 0, $widthPt, $heightPt], $pageOrientation);
        $dompdf->render();
        
        return $dompdf->output();
    }
}
