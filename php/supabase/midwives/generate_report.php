<?php
/**
 * Report Generation System for Midwives
 * Generates various types of reports with filtering and export options
 */

session_start();
require_once '../../../database/SupabaseConfig.php';
require_once '../../../database/DatabaseHelper.php';
require_once '../shared/access_control.php';

header('Content-Type: application/json');

// Check if user is midwife and can generate reports
if (!isset($_SESSION['midwife_id']) || !isApprovedMidwife()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - Midwife account not approved']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$report_type = $input['report_type'] ?? '';
$start_date = $input['start_date'] ?? '';
$end_date = $input['end_date'] ?? '';
$vaccine_type = $input['vaccine_type'] ?? '';
$status = $input['status'] ?? '';
$format = $input['format'] ?? 'csv';

try {
    switch ($report_type) {
        case 'immunization':
            $data = generateImmunizationReport($start_date, $end_date, $vaccine_type, $status);
            break;
        case 'child_health':
            $data = generateChildHealthReport($start_date, $end_date);
            break;
        case 'vaccination_schedule':
            $data = generateVaccinationScheduleReport($start_date, $end_date);
            break;
        case 'system_activity':
            $data = generateSystemActivityReport($start_date, $end_date);
            break;
        default:
            throw new Exception('Invalid report type');
    }
    
    $html_content = generateReportHTML($data, $report_type);
    $download_url = generateDownloadFile($data, $format, $report_type);
    
    echo json_encode([
        'status' => 'success',
        'html_content' => $html_content,
        'download_url' => $download_url,
        'data_count' => count($data),
        'report_type' => $report_type,
        'date_range' => $start_date . ' to ' . $end_date
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

/**
 * Generate Immunization Report
 */
function generateImmunizationReport($start_date, $end_date, $vaccine_type, $status) {
    $conditions = [];
    
    if ($start_date && $end_date) {
        $conditions['date_given.gte'] = $start_date;
        $conditions['date_given.lte'] = $end_date;
    }
    
    if ($vaccine_type) {
        $conditions['vaccine_name'] = $vaccine_type;
    }
    
    if ($status) {
        $conditions['status'] = $status;
    }
    
    $records = supabaseSelect('immunization_records', '*', $conditions, 'date_given.desc', 1000);
    
    if (!$records) {
        return [];
    }
    
    // Enhance with child information
    $enhanced_records = [];
    foreach ($records as $record) {
        $child_info = supabaseSelect('child_health_records', 'child_fname,child_lname,mother_fname,mother_lname', 
            ['baby_id' => $record['baby_id']], null, 1);
        
        if ($child_info && count($child_info) > 0) {
            $record['child_name'] = $child_info[0]['child_fname'] . ' ' . $child_info[0]['child_lname'];
            $record['mother_name'] = $child_info[0]['mother_fname'] . ' ' . $child_info[0]['mother_lname'];
        } else {
            $record['child_name'] = 'Unknown Child';
            $record['mother_name'] = 'Unknown Mother';
        }
        
        $enhanced_records[] = $record;
    }
    
    return $enhanced_records;
}

/**
 * Generate Child Health Report
 */
function generateChildHealthReport($start_date, $end_date) {
    $conditions = [];
    
    if ($start_date && $end_date) {
        $conditions['date_created.gte'] = $start_date;
        $conditions['date_created.lte'] = $end_date;
    }
    
    return supabaseSelect('child_health_records', '*', $conditions, 'date_created.desc', 1000);
}

/**
 * Generate Vaccination Schedule Report
 */
function generateVaccinationScheduleReport($start_date, $end_date) {
    $conditions = [];
    
    if ($start_date && $end_date) {
        $conditions['schedule_date.gte'] = $start_date;
        $conditions['schedule_date.lte'] = $end_date;
    }
    
    $records = supabaseSelect('immunization_records', '*', $conditions, 'schedule_date.asc', 1000);
    
    if (!$records) {
        return [];
    }
    
    // Enhance with child information
    $enhanced_records = [];
    foreach ($records as $record) {
        $child_info = supabaseSelect('child_health_records', 'child_fname,child_lname', 
            ['baby_id' => $record['baby_id']], null, 1);
        
        if ($child_info && count($child_info) > 0) {
            $record['child_name'] = $child_info[0]['child_fname'] . ' ' . $child_info[0]['child_lname'];
        } else {
            $record['child_name'] = 'Unknown Child';
        }
        
        $enhanced_records[] = $record;
    }
    
    return $enhanced_records;
}

/**
 * Generate System Activity Report
 */
function generateSystemActivityReport($start_date, $end_date) {
    $conditions = [];
    
    if ($start_date && $end_date) {
        $conditions['created_at.gte'] = $start_date;
        $conditions['created_at.lte'] = $end_date;
    }
    
    return supabaseSelect('activity_logs', '*', $conditions, 'created_at.desc', 1000);
}

/**
 * Generate HTML content for the report
 */
function generateReportHTML($data, $report_type) {
    if (empty($data)) {
        return '<p>No data found for the selected criteria.</p>';
    }
    
    $html = '<div class="report-table">';
    $html .= '<table class="table">';
    $html .= '<thead><tr>';
    
    // Generate headers based on report type
    switch ($report_type) {
        case 'immunization':
            $headers = ['Child Name', 'Mother Name', 'Vaccine', 'Dose', 'Date Given', 'Status', 'Administered By'];
            break;
        case 'child_health':
            $headers = ['Child Name', 'Birth Date', 'Mother Name', 'Address', 'Status', 'Date Created'];
            break;
        case 'vaccination_schedule':
            $headers = ['Child Name', 'Vaccine', 'Schedule Date', 'Status', 'Catch-up Date'];
            break;
        case 'system_activity':
            $headers = ['User Type', 'Action', 'Description', 'Date', 'IP Address'];
            break;
        default:
            $headers = ['Data'];
    }
    
    foreach ($headers as $header) {
        $html .= '<th>' . $header . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    
    // Generate rows
    foreach ($data as $row) {
        $html .= '<tr>';
        switch ($report_type) {
            case 'immunization':
                $html .= '<td>' . ($row['child_name'] ?? '') . '</td>';
                $html .= '<td>' . ($row['mother_name'] ?? '') . '</td>';
                $html .= '<td>' . ($row['vaccine_name'] ?? '') . '</td>';
                $html .= '<td>' . ($row['dose_number'] ?? '') . '</td>';
                $html .= '<td>' . ($row['date_given'] ?? '') . '</td>';
                $html .= '<td>' . ($row['status'] ?? '') . '</td>';
                $html .= '<td>' . ($row['administered_by'] ?? '') . '</td>';
                break;
            case 'child_health':
                $html .= '<td>' . ($row['child_fname'] ?? '') . ' ' . ($row['child_lname'] ?? '') . '</td>';
                $html .= '<td>' . ($row['birth_date'] ?? '') . '</td>';
                $html .= '<td>' . ($row['mother_fname'] ?? '') . ' ' . ($row['mother_lname'] ?? '') . '</td>';
                $html .= '<td>' . ($row['address'] ?? '') . '</td>';
                $html .= '<td>' . ($row['status'] ?? '') . '</td>';
                $html .= '<td>' . ($row['date_created'] ?? '') . '</td>';
                break;
            case 'vaccination_schedule':
                $html .= '<td>' . ($row['child_name'] ?? '') . '</td>';
                $html .= '<td>' . ($row['vaccine_name'] ?? '') . '</td>';
                $html .= '<td>' . ($row['schedule_date'] ?? '') . '</td>';
                $html .= '<td>' . ($row['status'] ?? '') . '</td>';
                $html .= '<td>' . ($row['catch_up_date'] ?? '') . '</td>';
                break;
            case 'system_activity':
                $html .= '<td>' . ($row['user_type'] ?? '') . '</td>';
                $html .= '<td>' . ($row['action'] ?? '') . '</td>';
                $html .= '<td>' . ($row['description'] ?? '') . '</td>';
                $html .= '<td>' . ($row['created_at'] ?? '') . '</td>';
                $html .= '<td>' . ($row['ip_address'] ?? '') . '</td>';
                break;
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    $html .= '</div>';
    
    // Add summary statistics
    $html .= '<div class="report-summary">';
    $html .= '<h4>Report Summary</h4>';
    $html .= '<p>Total Records: ' . count($data) . '</p>';
    $html .= '<p>Generated: ' . date('Y-m-d H:i:s') . '</p>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Generate download file
 */
function generateDownloadFile($data, $format, $report_type) {
    $filename = $report_type . '_report_' . date('Y-m-d_H-i-s');
    
    // Create temp directory if it doesn't exist
    $temp_dir = '../../../temp/';
    if (!file_exists($temp_dir)) {
        mkdir($temp_dir, 0755, true);
    }
    
    switch ($format) {
        case 'csv':
            $filename .= '.csv';
            $content = generateCSV($data, $report_type);
            break;
        case 'pdf':
            $filename .= '.pdf';
            $content = generatePDF($data, $report_type);
            break;
        case 'excel':
            $filename .= '.xlsx';
            $content = generateExcel($data, $report_type);
            break;
        default:
            $filename .= '.csv';
            $content = generateCSV($data, $report_type);
    }
    
    // Save file and return download URL
    $file_path = $temp_dir . $filename;
    file_put_contents($file_path, $content);
    
    return '../../temp/' . $filename;
}

/**
 * Generate CSV content
 */
function generateCSV($data, $report_type) {
    $csv = '';
    
    // Add headers
    switch ($report_type) {
        case 'immunization':
            $csv .= "Child Name,Mother Name,Vaccine,Dose,Date Given,Status,Administered By\n";
            break;
        case 'child_health':
            $csv .= "Child Name,Birth Date,Mother Name,Address,Status,Date Created\n";
            break;
        case 'vaccination_schedule':
            $csv .= "Child Name,Vaccine,Schedule Date,Status,Catch-up Date\n";
            break;
        case 'system_activity':
            $csv .= "User Type,Action,Description,Date,IP Address\n";
            break;
    }
    
    // Add data rows
    foreach ($data as $row) {
        $csv_row = '';
        switch ($report_type) {
            case 'immunization':
                $csv_row .= '"' . ($row['child_name'] ?? '') . '",';
                $csv_row .= '"' . ($row['mother_name'] ?? '') . '",';
                $csv_row .= '"' . ($row['vaccine_name'] ?? '') . '",';
                $csv_row .= '"' . ($row['dose_number'] ?? '') . '",';
                $csv_row .= '"' . ($row['date_given'] ?? '') . '",';
                $csv_row .= '"' . ($row['status'] ?? '') . '",';
                $csv_row .= '"' . ($row['administered_by'] ?? '') . '"';
                break;
            case 'child_health':
                $csv_row .= '"' . ($row['child_fname'] ?? '') . ' ' . ($row['child_lname'] ?? '') . '",';
                $csv_row .= '"' . ($row['birth_date'] ?? '') . '",';
                $csv_row .= '"' . ($row['mother_fname'] ?? '') . ' ' . ($row['mother_lname'] ?? '') . '",';
                $csv_row .= '"' . ($row['address'] ?? '') . '",';
                $csv_row .= '"' . ($row['status'] ?? '') . '",';
                $csv_row .= '"' . ($row['date_created'] ?? '') . '"';
                break;
            case 'vaccination_schedule':
                $csv_row .= '"' . ($row['child_name'] ?? '') . '",';
                $csv_row .= '"' . ($row['vaccine_name'] ?? '') . '",';
                $csv_row .= '"' . ($row['schedule_date'] ?? '') . '",';
                $csv_row .= '"' . ($row['status'] ?? '') . '",';
                $csv_row .= '"' . ($row['catch_up_date'] ?? '') . '"';
                break;
            case 'system_activity':
                $csv_row .= '"' . ($row['user_type'] ?? '') . '",';
                $csv_row .= '"' . ($row['action'] ?? '') . '",';
                $csv_row .= '"' . ($row['description'] ?? '') . '",';
                $csv_row .= '"' . ($row['created_at'] ?? '') . '",';
                $csv_row .= '"' . ($row['ip_address'] ?? '') . '"';
                break;
        }
        $csv .= $csv_row . "\n";
    }
    
    return $csv;
}

/**
 * Generate PDF content (simplified - returns HTML for now)
 */
function generatePDF($data, $report_type) {
    // For now, return HTML that can be converted to PDF
    return generateReportHTML($data, $report_type);
}

/**
 * Generate Excel content (simplified - returns CSV for now)
 */
function generateExcel($data, $report_type) {
    // For now, return CSV format
    return generateCSV($data, $report_type);
}
?>
