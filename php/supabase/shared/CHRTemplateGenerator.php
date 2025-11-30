<?php

class CHRTemplateGenerator {
    private $childData;
    private $immunizationData;
    private $feedingData;
    private $tdData;
    
    public function __construct($childData, $immunizationData = [], $feedingData = [], $tdData = []) {
        $this->childData = $childData;
        $this->immunizationData = $immunizationData;
        $this->feedingData = $feedingData;
        $this->tdData = $tdData;
    }
    
    /**
     * Generate the complete CHR HTML template using pure table layout
     */
    public function generateHTML() {
        $html = $this->getHTMLHead();
        $html .= $this->getTableBasedContent();
        $html .= $this->getHTMLFooter();
        
        return $html;
    }
    
    /**
     * Get HTML head with minimal CSS for DOMPDF
     */
    private function getHTMLHead() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CHR Format Preview</title>
    <style>
        @page {
            size: 8.5in 13in;
            margin: 0.5in;
        }
        body {
            font-family: "DejaVu Sans", "Arial Unicode MS", "Liberation Sans", "Times New Roman", serif;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
        }
    </style>
</head>';
    }
    
    /**
     * Get the main content using pure table layout - optimized for single page
     */
    private function getTableBasedContent() {
        $child = $this->childData;
        $td = $this->tdData;
        
        return '<body>
        <table style="width: 100%; border-collapse: collapse;">
            <!-- Header Row -->
            <tr>
                <td style="text-align: center; font-size: 13pt; font-weight: bold; padding: 0px;" colspan="2">
                    CHILD HEALTH RECORD
                </td>
            </tr>
            <tr>
                <td style="text-align: center; font-size: 8pt; padding-bottom: 0px;" colspan="2">
                    City Health Department, Ormoc City
                </td>
            </tr>
            
            <!-- Personal Information Row -->
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 4px;">
                    <table style="width: 100%;">
                        <tr><td style="font-size: 10pt; padding: 0px;">Name of Child: ' . htmlspecialchars($child['name'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Gender: ' . htmlspecialchars($child['child_gender'] ?? $child['gender'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Date of Birth: ' . htmlspecialchars($this->formatDate($child['child_birth_date'] ?? '')) . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Place of Birth: ' . htmlspecialchars($child['place_of_birth'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Birth Weight: ' . htmlspecialchars($child['birth_weight'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Birth Length: ' . htmlspecialchars($child['birth_height'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Address: ' . htmlspecialchars($child['address'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Allergies: ' . htmlspecialchars($child['allergies'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Blood Type: ' . htmlspecialchars($child['blood_type'] ?? '') . '</td></tr>
                    </table>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 4px;">
                    <table style="width: 100%;">
                        <tr><td style="font-size: 10pt; padding: 0px;">Family Number: ' . htmlspecialchars($child['family_number'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Philhealth No.: ' . htmlspecialchars(($child['philhealth'] ?? $child['philhealth_no'] ?? '')) . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">NHTS: ' . htmlspecialchars($child['nhts'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Non-NHTS: ' . htmlspecialchars($child['non_nhts'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Father\'s Name: ' . htmlspecialchars($child['father_name'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Mother\'s Name: ' . htmlspecialchars($child['mother_name'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">LMP: ' . htmlspecialchars($child['lpm'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Family Planning: ' . htmlspecialchars($child['family_planning'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;"></td></tr>
                    </table>
                </td>
            </tr>
            
            <!-- Child History Header -->
            <tr>
                <td style="text-align: center; font-size: 10pt; text-decoration: underline; font-weight: bold; padding: 0px;" colspan="2">
                    CHILD HISTORY
                </td>
            </tr>
            
            <!-- Child History Content -->
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 4px;">
                    <table style="width: 100%;">
                        <tr><td style="font-size: 10pt; padding: 0px;">Date of Newbornscreening: ' . htmlspecialchars($this->formatDate($child['date_newbornscreening'] ?? $child['nbs_date'] ?? '')) . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Type of Delivery: ' . $this->getDeliveryTypeCheckbox($child['delivery_type'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Birth Order: ' . $this->getBirthOrderCheckbox($child['birth_order'] ?? '') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">Attended by: ' . $this->getAttendedByCheckboxes($child['birth_attendant'] ?? '') . '</td></tr>
                    </table>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 4px;">
                    <table style="width: 100%;">
                        <tr><td style="font-size: 10pt; padding: 0px;">Place of Newbornscreening: ' . htmlspecialchars($child['placenewbornscreening'] ?? $child['nbs_place'] ?? '__________') . '</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">( ) Caesarean Section</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">( ) Twin</td></tr>
                        <tr><td style="font-size: 10pt; padding: 0px;">( ) Others: ' . htmlspecialchars($child['birth_attendant_other'] ?? '__________') . '</td></tr>
                    </table>
                </td>
            </tr>
            
            <!-- Feeding and TD Tables Row -->
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 4px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: bold; text-decoration: underline; font-size: 10pt; padding: 0px;" colspan="2">
                                Exclusive Breastfeeding/Complimentary Feeding:
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 10pt; padding: 0px;">' . $this->getFeedingMonths(1, 4, $child) . '</td>
                            <td style="font-size: 10pt; padding: 0px;">6th mo.' . $this->getFeedingCheckbox($child['exclusive_breastfeeding_6mo'] ?? false) . ' food: ' . htmlspecialchars($child['complementary_feeding_6mo'] ?? '') . '</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10pt; padding: 0px;">' . $this->getFeedingMonths(2, 5, $child) . '</td>
                            <td style="font-size: 10pt; padding: 0px;">7th mo.' . $this->getFeedingCheckbox($child['exclusive_breastfeeding_7mo'] ?? false) . ' food: ' . htmlspecialchars($child['complementary_feeding_7mo'] ?? '') . '</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10pt; padding: 0px;">' . $this->getFeedingMonths(3, 6, $child) . '</td>
                            <td style="font-size: 10pt; padding: 0px;">8th mo.' . $this->getFeedingCheckbox($child['exclusive_breastfeeding_8mo'] ?? false) . ' food: ' . htmlspecialchars($child['complementary_feeding_8mo'] ?? '') . '</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 4px;">
                    <table style="width: 100%;  border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: bold; text-decoration: underline; font-size: 10pt; padding: 0px;" colspan="2">
                                TD Status (date pls.)
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 10pt; padding: 0px;">TD 1st dose: ' . htmlspecialchars($this->formatDate($td['dose1_date'] ?? '')) . ' TD 4th dose: ' . htmlspecialchars($this->formatDate($td['dose4_date'] ?? '')) . '</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10pt; padding: 0px;">TD 2nd dose: ' . htmlspecialchars($this->formatDate($td['dose2_date'] ?? '')) . ' TD 5th dose: ' . htmlspecialchars($this->formatDate($td['dose5_date'] ?? '')) . '</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10pt; padding: 0px;">TD 3rd dose: ' . htmlspecialchars($this->formatDate($td['dose3_date'] ?? '')) . '</td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <!-- Immunization Header -->
            <tr>
                <td style="text-align: center; font-size: 10pt; text-decoration: underline; font-weight: bold; padding: 0px;" colspan="2">
                    IMMUNIZATION RECORD (pls. put the date)
                </td>
            </tr>
            
            <!-- Immunization Content -->
            <tr>
                <td colspan="2" style="padding: 0;">
                    ' . $this->getImmunizationTable() . '
                </td>
            </tr>
            
            <!-- Ledger Table -->
            <tr>
                <td colspan="2">
                    ' . $this->getLedgerTable() . '
                </td>
            </tr>
        </table>
        </body>';
    }
    
    /**
     * Get immunization section with specific column layout as requested:
     * Row 1: BCG | Scar (2 columns)
     * Row 2: Hepa B within 24 hrs | Hepa B more than 24 hrs (2 columns)
     * Row 3: Pentavalent 1st | Pentavalent 2nd | Pentavalent 3rd (3 columns)
     * Row 4: bOPV 1st | bOPV 2nd | bOPV 3rd | IPV (4 columns)
     * Row 5: PCV 1st | PCV 2nd | PCV 3rd (3 columns)
     * Row 6: MMR 1st | MMR 2nd | FIC | CIC (4 columns)
     * Row 7: Other Vaccines (1 column)
     * 
     * UPDATED: 2025-01-20 17:45 - FORCED PIXEL-BASED WIDTHS with table-layout: fixed
     * - BCG: 1.5in | Scar: 6.5in (BCG gets minimal space)
     * - Hepa B within 24hrs: 1.5in | Hepa B more than 24hrs: 6.5in
     * - Pentavalent 1st: 1.2in | 2nd: 1.5in | 3rd: 5.3in (3rd gets most space)
     * - bOPV 1st: 0.8in | 2nd: 1.0in | 3rd: 1.0in | IPV: 5.2in (IPV gets most space)
     * - PCV 1st: 1.2in | 2nd: 1.5in | 3rd: 5.3in (3rd gets most space)
     * - MMR 1st: 0.8in | 2nd: 1.0in | FIC: 2.8in | CIC: 3.4in (CIC gets most space)
     */
    private function getImmunizationTable() {
        $html = '<table style="width: 100%; font-family: \'Times New Roman\', Times, serif; letter-spacing: -0.1pt;">'
            // Row 1: BCG | Scar (2 columns)
            . '<tr>'
                . '<td style="font-size: 10pt; padding: 0px; width: 25%; white-space: nowrap;">BCG: ' . $this->getImmunizationDate('BCG') . '</td>'
                . '<td style="font-size: 10pt; padding: 0px; width: 75%; white-space: nowrap;">Scar: ' . $this->getScarStatus() . '</td>'
            . '</tr>'
            // Row 2: Hepa B within 24 hrs | Hepa B more than 24 hrs (2 columns)
            . '<tr>'
                . '<td style="font-size: 10pt; padding: 0px; width: 25%; white-space: nowrap;">Hepa B within 24 hrs: ' . $this->getImmunizationDate('HEPAB1 (w/in 24 hrs)') . '</td>'
                . '<td style="font-size: 10pt; padding: 0px; width: 75%; white-space: nowrap;">Hepa B more than 24 hrs: ' . $this->getImmunizationDate('HEPAB1 (More than 24hrs)') . '</td>'
            . '</tr>'
            // Row 3: Pentavalent 1st | Pentavalent 2nd | Pentavalent 3rd (3 columns)
            . '<tr>'
                . '<td style="font-size: 10pt; padding: 0px; width: 20%; white-space: nowrap;">Pentavalent 1st dose: ' . $this->getImmunizationDate('Pentavalent (DPT-HepB-Hib) - 1st') . '</td>'
                . '<td style="font-size: 10pt; padding: 0px; width: 25%; white-space: nowrap;">Pentavalent 2nd dose: ' . $this->getImmunizationDate('Pentavalent (DPT-HepB-Hib) - 2nd') . '</td>'
                . '<td style="font-size: 10pt; padding: 0px; width: 55%; white-space: nowrap;">Pentavalent 3rd dose: ' . $this->getImmunizationDate('Pentavalent (DPT-HepB-Hib) - 3rd') . '</td>'
            . '</tr>'
            // Row 4: bOPV 1st | bOPV 2nd | bOPV 3rd | IPV (4 columns)
            . '<tr>'
                . '<td style="font-size: 10pt; padding: 0px; width: 12%; white-space: nowrap;">bOPV 1st dose: ' . $this->getImmunizationDate('OPV - 1st') . '</td>'
                . '<td style="font-size: 10pt; padding: 0px; width: 15%; white-space: nowrap;">bOPV 2nd dose: ' . $this->getImmunizationDate('OPV - 2nd') . '</td>'
                . '<td style="font-size: 10pt; padding: 0px; width: 15%; white-space: nowrap;">bOPV 3rd dose: ' . $this->getImmunizationDate('OPV - 3rd') . '</td>'
                . '<td style="font-size: 10pt; padding: 0px; width: 58%; white-space: nowrap;">IPV: ' . $this->getImmunizationDate('IPV') . '</td>'
            . '</tr>'
            // Row 5: PCV 1st | PCV 2nd | PCV 3rd (3 columns)
            . '<tr>'
                . '<td style="font-size: 10pt; padding: 0px; width: 20%; white-space: nowrap;">PCV 1st dose: ' . $this->getImmunizationDate('PCV - 1st') . '</td>'
                . '<td style="font-size: 10pt; padding: 0px; width: 25%; white-space: nowrap;">PCV 2nd dose: ' . $this->getImmunizationDate('PCV - 2nd') . '</td>'
                . '<td style="font-size: 10pt; padding: 0px; width: 55%; white-space: nowrap;">PCV 3rd dose: ' . $this->getImmunizationDate('PCV - 3rd') . '</td>'
            . '</tr>'
            // Row 6: MMR 1st | MMR 2nd | FIC | CIC (4 columns)
            . '<tr>'
                . '<td style="font-size: 10pt; padding: 0px; width: 8%; white-space: nowrap;">MMR 1st dose: ' . $this->getImmunizationDate('MCV1 (AMV)') . '</td>'
                . '<td style="font-size: 10pt; padding: 0px; width: 10%; white-space: nowrap;">MMR 2nd dose: ' . $this->getImmunizationDate('MCV2 (MMR)') . '</td>'
                . '<td style="font-size: 10pt; padding: 0px; width: 32%; white-space: nowrap;">FIC: ' . $this->getImmunizationDate('FIC') . '</td>'
                . '<td style="font-size: 10pt; padding: 0px; width: 50%; white-space: nowrap;">CIC: ' . $this->getImmunizationDate('CIC') . '</td>'
            . '</tr>'
            // Row 7: Other Vaccines (1 column)
            . '<tr>'
                . '<td style="font-size: 10pt; padding: 0px; width: 100%; white-space: nowrap;">Other Vaccines: ' . htmlspecialchars($this->getOtherVaccines()) . '</td>'
            . '</tr>';

        $html .= '</table>';
        return $html;
    }
    
    /**
     * Get ledger table with proper column widths
     */
    private function getLedgerTable() {
        $html = '<table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-top: 0px;">
            <tr>
                <th style="width: 8%; border: 1px solid #000; padding: 0px; font-size: 8pt; font-weight: bold; text-align: center;">Date</th>
                <th style="width: 12%; border: 1px solid #000; padding: 0px; font-size: 8pt; font-weight: bold; text-align: center;">Purpose</th>
                <th style="width: 6%; border: 1px solid #000; padding: 0px; font-size: 8pt; font-weight: bold; text-align: center;">HT</th>
                <th style="width: 6%; border: 1px solid #000; padding: 0px; font-size: 8pt; font-weight: bold; text-align: center;">WT</th>
                <th style="width: 8%; border: 1px solid #000; padding: 0px; font-size: 8pt; font-weight: bold; text-align: center;">MUAC</th>
                <th style="width: 8%; border: 1px solid #000; padding: 0px; font-size: 8pt; font-weight: bold; text-align: center;">STATUS</th>
                <th style="width: 18%; border: 1px solid #000; padding: 0px; font-size: 8pt; font-weight: bold; text-align: center;">Condition of Baby</th>
                <th style="width: 18%; border: 1px solid #000; padding: 0px; font-size: 8pt; font-weight: bold; text-align: center;">Advice Given</th>
                <th style="width: 10%; border: 1px solid #000; padding: 0px; font-size: 8pt; font-weight: bold; text-align: center;">Next Sched Date</th>
                <th style="width: 6%; border: 1px solid #000; padding: 0px; font-size: 8pt; font-weight: bold; text-align: center;">Remarks</th>
            </tr>';
        
        // Add actual vaccination records
        $vaccinationRecords = $this->getVaccinationRecords();
        foreach ($vaccinationRecords as $record) {
            $html .= '<tr>
                <td style="border: 1px solid #000; padding: 0px; font-size: 8pt; text-align: center;">' . htmlspecialchars($this->formatDate($record['date'])) . '</td>
                <td style="border: 1px solid #000; padding: 0px; font-size: 8pt; text-align: left;">' . htmlspecialchars($record['purpose']) . '</td>
                <td style="border: 1px solid #000; padding: 0px; font-size: 8pt; text-align: center;">' . htmlspecialchars($record['height']) . '</td>
                <td style="border: 1px solid #000; padding: 0px; font-size: 8pt; text-align: center;">' . htmlspecialchars($record['weight']) . '</td>
                <td style="border: 1px solid #000; padding: 0px; font-size: 8pt; text-align: center;">' . htmlspecialchars($record['muac']) . '</td>
                <td style="border: 1px solid #000; padding: 0px; font-size: 8pt; text-align: center;">' . htmlspecialchars($record['status']) . '</td>
                <td style="border: 1px solid #000; padding: 0px; font-size: 8pt; text-align: left;">' . htmlspecialchars($record['condition']) . '</td>
                <td style="border: 1px solid #000; padding: 0px; font-size: 8pt; text-align: left;">' . htmlspecialchars($record['advice']) . '</td>
                <td style="border: 1px solid #000; padding: 0px; font-size: 8pt; text-align: center;">' . htmlspecialchars($record['next_schedule']) . '</td>
                <td style="border: 1px solid #000; padding: 0px; font-size: 8pt; text-align: center;">' . htmlspecialchars($record['remarks']) . '</td>
            </tr>';
        }
        
        // Fill remaining rows with empty cells - 18 rows total
        $remainingRows = 18 - count($vaccinationRecords);
        for ($i = 0; $i < $remainingRows; $i++) {
            $html .= '<tr>
                <td style="border: 1px solid #000; padding: 0px;">&nbsp;</td>
                <td style="border: 1px solid #000; padding: 0px;"></td>
                <td style="border: 1px solid #000; padding: 0px;"></td>
                <td style="border: 1px solid #000; padding: 0px;"></td>
                <td style="border: 1px solid #000; padding: 0px;"></td>
                <td style="border: 1px solid #000; padding: 0px;"></td>
                <td style="border: 1px solid #000; padding: 0px;"></td>
                <td style="border: 1px solid #000; padding: 0px;"></td>
                <td style="border: 1px solid #000; padding: 0px;"></td>
                <td style="border: 1px solid #000; padding: 0px;"></td>
            </tr>';
        }
        
        $html .= '</table>';
        
        return $html;
    }
    
    /**
     * Helper methods for checkbox generation
     */
    private function getDeliveryTypeCheckbox($deliveryType) {
        $isNormal = strtolower($deliveryType) === 'normal';
        return $isNormal ? '(•) Normal' : '( ) Normal';
    }
    
    private function getBirthOrderCheckbox($birthOrder) {
        $isSingle = strtolower($birthOrder) === 'single';
        return $isSingle ? '(•) Single' : '( ) Single';
    }
    
    private function getAttendedByCheckboxes($attendant) {
        $attendant = strtolower($attendant);
        $doctor = strpos($attendant, 'doctor') !== false ? '(•)' : '( )';
        $midwife = strpos($attendant, 'midwife') !== false ? '(•)' : '( )';
        $nurse = strpos($attendant, 'nurse') !== false ? '(•)' : '( )';
        $hilot = strpos($attendant, 'hilot') !== false ? '(•)' : '( )';
        
        return "$doctor Doctor $midwife Midwife $nurse Nurse $hilot Hilot";
    }
    
    private function getFeedingMonths($month1, $month2, $child) {
        $check1 = $child["exclusive_breastfeeding_{$month1}mo"] ? '•' : '';
        $check2 = $child["exclusive_breastfeeding_{$month2}mo"] ? '•' : '';
        return "{$month1}st mo.({$check1}) {$month2}th mo.({$check2})";
    }
    
    private function getFeedingCheckbox($isChecked) {
        return $isChecked ? '(•)' : '( )';
    }
    
    private function formatDate($dateString) {
        if (empty($dateString)) {
            return '';
        }
        
        try {
            $date = new DateTime($dateString);
            return $date->format('j,M,Y'); // e.g., "15,Jan,2024" or "15-Jan-2024"
        } catch (Exception $e) {
            return $dateString; // Return original if parsing fails
        }
    }
    
    private function getImmunizationDate($vaccineName) {
        // Use real data
        foreach ($this->immunizationData as $record) {
            if (($record['vaccine_name'] ?? '') === $vaccineName && ($record['status'] ?? '') === 'taken') {
                return $this->formatDate($record['date_given'] ?? '');
            }
        }
        return '';
    }
    
    private function getScarStatus() {
        // Only show Yes if actual data indicates a scar
        foreach ($this->immunizationData as $record) {
            if (($record['vaccine_name'] ?? '') === 'BCG' && !empty($record['scar_status'])) {
                $scarStatus = strtolower(trim((string)$record['scar_status']));
                if ($scarStatus === 'yes' || $scarStatus === 'y') {
                    return 'Yes';
                }
            }
        }
        return '';
    }
    
    private function getOtherVaccines() {
        $otherVaccines = [];
        $known = [
            'BCG', 'HEPAB1 (w/in 24 hrs)', 'HEPAB1 (More than 24hrs)',
            'Pentavalent (DPT-HepB-Hib) - 1st', 'Pentavalent (DPT-HepB-Hib) - 2nd', 'Pentavalent (DPT-HepB-Hib) - 3rd',
            'OPV - 1st', 'OPV - 2nd', 'OPV - 3rd', 'IPV',
            'PCV - 1st', 'PCV - 2nd', 'PCV - 3rd',
            'MCV1 (AMV)', 'MCV2 (MMR)', 'CIC', 'FIC'
        ];
        foreach ($this->immunizationData as $record) {
            if (!in_array(($record['vaccine_name'] ?? ''), $known, true) && ($record['status'] ?? '') === 'taken') {
                $otherVaccines[] = ($record['vaccine_name'] ?? '') . ' ' . $this->formatDate($record['date_given'] ?? '');
            }
        }
        return implode(', ', $otherVaccines);
    }
    
    private function getVaccinationRecords() {
        // Canonical vaccine order (same as frontend)
        $canonical = [
            ['key' => 'bcg', 'aliases' => ['bcg']],
            ['key' => 'hepb_birth', 'aliases' => ['hepatitis b', 'hepab1 (w/in 24 hrs)', 'hepab1 (more than 24hrs)']],
            ['key' => 'penta1', 'aliases' => ['pentavalent (dpt-hepb-hib) - 1st', 'pentavalent 1']],
            ['key' => 'opv1', 'aliases' => ['opv - 1st', 'opv 1']],
            ['key' => 'pcv1', 'aliases' => ['pcv - 1st', 'pcv 1']],
            ['key' => 'rota1', 'aliases' => ['rota virus vaccine - 1st', 'rota 1']],
            ['key' => 'penta2', 'aliases' => ['pentavalent (dpt-hepb-hib) - 2nd', 'pentavalent 2']],
            ['key' => 'opv2', 'aliases' => ['opv - 2nd', 'opv 2']],
            ['key' => 'pcv2', 'aliases' => ['pcv - 2nd', 'pcv 2']],
            ['key' => 'rota2', 'aliases' => ['rota virus vaccine - 2nd', 'rota 2']],
            ['key' => 'penta3', 'aliases' => ['pentavalent (dpt-hepb-hib) - 3rd', 'pentavalent 3']],
            ['key' => 'opv3', 'aliases' => ['opv - 3rd', 'opv 3']],
            ['key' => 'pcv3', 'aliases' => ['pcv - 3rd', 'pcv 3']],
            ['key' => 'mcv1', 'aliases' => ['mcv1 (amv)', 'mcv1']],
            ['key' => 'mcv2', 'aliases' => ['mcv2 (mmr)', 'mcv2']]
        ];

        // Build alias lookup
        $aliasLookup = [];
        foreach ($canonical as $entry) {
            foreach ($entry['aliases'] as $alias) {
                $aliasLookup[strtolower(trim($alias))] = $entry;
            }
        }

        // Normalize vaccine name
        $normalizeVaccine = function($name) use ($aliasLookup) {
            if (empty($name)) return null;
            $key = strtolower(trim($name));
            return $aliasLookup[$key] ?? null;
        };

        // Group records by vaccine key
        $recordsByKey = [];
        foreach ($this->immunizationData as $r) {
            $normalized = $normalizeVaccine($r['vaccine_name'] ?? '');
            if (!$normalized) continue;
            $key = $normalized['key'];
            if (!isset($recordsByKey[$key])) {
                $recordsByKey[$key] = [];
            }
            $recordsByKey[$key][] = $r;
        }

        // Function to get next vaccine record in canonical sequence
        $getNextReferenceRecord = function($currentKey) use ($canonical, $recordsByKey) {
            $currentIndex = -1;
            foreach ($canonical as $idx => $entry) {
                if ($entry['key'] === $currentKey) {
                    $currentIndex = $idx;
                    break;
                }
            }
            if ($currentIndex === -1) return null;

            // Get the next vaccine in sequence
            for ($i = $currentIndex + 1; $i < count($canonical); $i++) {
                $entry = $canonical[$i];
                $records = $recordsByKey[$entry['key']] ?? [];
                if (empty($records)) continue;

                // Priority: upcoming (if exists) > taken (if exists) > any
                $upcoming = null;
                $taken = null;
                foreach ($records as $r) {
                    $status = strtolower($r['status'] ?? '');
                    if (($status === 'scheduled' || $status === 'upcoming') && !$upcoming) {
                        $upcoming = $r;
                    }
                    if (($status === 'taken' || $status === 'completed') && !$taken) {
                        $taken = $r;
                    }
                }
                return $upcoming ?? $taken ?? $records[0];
            }
            return null;
        };

        // Function to calculate next schedule date with priority
        $getNextScheduleDate = function($nextRecord) {
            if (!$nextRecord) return '';
            
            $dateValue = '';
            // Priority: batch_schedule_date > catch_up_date > schedule_date
            if (!empty($nextRecord['batch_schedule_date']) && trim($nextRecord['batch_schedule_date']) !== '') {
                $dateValue = trim($nextRecord['batch_schedule_date']);
            } elseif (!empty($nextRecord['catch_up_date']) && trim($nextRecord['catch_up_date']) !== '') {
                $dateValue = trim($nextRecord['catch_up_date']);
            } elseif (!empty($nextRecord['schedule_date']) && trim($nextRecord['schedule_date']) !== '') {
                $dateValue = trim($nextRecord['schedule_date']);
            }
            
            if ($dateValue) {
                $formattedDate = $this->formatDate($dateValue);
                return $formattedDate ? $formattedDate : '';
            }
            return '';
        };

        $records = [];
        foreach ($this->immunizationData as $record) {
            if (($record['status'] ?? '') === 'taken' || ($record['status'] ?? '') === 'completed') {
                // Find next vaccine's schedule date
                $normalized = $normalizeVaccine($record['vaccine_name'] ?? '');
                $nextRecord = $normalized ? $getNextReferenceRecord($normalized['key']) : null;
                $nextSchedule = $getNextScheduleDate($nextRecord);

                $records[] = [
                    'date' => $this->formatDate($record['date_given'] ?? ''),
                    'purpose' => $record['vaccine_name'] ?? '',
                    'height' => $record['height'] ?? '',
                    'weight' => $record['weight'] ?? '',
                    'muac' => $record['muac'] ?? '',
                    'status' => 'Taken',
                    'condition' => $record['condition_of_baby'] ?? '',
                    'advice' => $record['advice_given'] ?? '',
                    'next_schedule' => $nextSchedule,
                    'remarks' => $record['remarks'] ?? ''
                ];
            }
        }
        
        // Sort by date
        usort($records, function($a, $b) {
            $dateA = strtotime($a['date']);
            $dateB = strtotime($b['date']);
            if ($dateA === false) $dateA = 0;
            if ($dateB === false) $dateB = 0;
            return $dateA - $dateB;
        });
        
        return $records;
    }
    
    /**
     * Get HTML footer
     */
    private function getHTMLFooter() {
        return '</html>';
    }
}
?>