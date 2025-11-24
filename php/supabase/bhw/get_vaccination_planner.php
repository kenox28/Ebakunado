<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$vaccine = $_GET['vaccine'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'scheduled';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;

try {
    // Fetch all immunization records first (like get_immunization_view.php does)
    $immColumns = 'id,baby_id,vaccine_name,dose_number,status,schedule_date,batch_schedule_date,catch_up_date';
    
    // Fetch in batches to get all records
    $immunizations = [];
    $batchSize = 200;
    $offset = 0;
    while (true) {
        $batch = supabaseSelect(
            'immunization_records',
            $immColumns,
            [],
            'schedule_date.asc',
            $batchSize,
            $offset
        );
        if (!$batch || count($batch) === 0) break;
        $immunizations = array_merge($immunizations, $batch);
        if (count($batch) < $batchSize) break;
        $offset += $batchSize;
    }

    $filters = buildDateRange($startDate, $endDate);
    $todayStr = date('Y-m-d');
    
    // Determine if we need exact date match or date range
    $dateSel = '';
    if ($startDate && !$endDate) {
        // Single date provided - use exact match like get_immunization_view.php
        $dateSel = $startDate;
    }

    // Filter immunizations first (like get_immunization_view.php)
    $immsFiltered = array_values(array_filter($immunizations, function($r) use ($vaccine, $statusFilter, $filters, $todayStr, $dateSel) {
        // Vaccine filter
        if ($vaccine !== 'all') {
            if ((string)($r['vaccine_name'] ?? '') !== $vaccine) return false;
        }
        
        // Date filter: exact match (like get_immunization_view.php) or date range
        if ($dateSel !== '') {
            // Exact date match
            $sd = (string)($r['schedule_date'] ?? '');
            $bd = (string)($r['batch_schedule_date'] ?? '');
            $cd = (string)($r['catch_up_date'] ?? '');
            if ($sd !== $dateSel && $bd !== $dateSel && $cd !== $dateSel) return false;
        } else if ($filters['start'] !== null || $filters['end'] !== null) {
            // Date range filter
            $guidelineDate = (string)($r['schedule_date'] ?? '');
            $batchDate = (string)($r['batch_schedule_date'] ?? '');
            $catchUpDate = (string)($r['catch_up_date'] ?? '');
            $operationalDate = $batchDate ?: $guidelineDate ?: $catchUpDate;
            
            if ($operationalDate === '') return false;
            
            if ($filters['start'] !== null && $operationalDate < $filters['start']) {
                return false;
            }
            if ($filters['end'] !== null && $operationalDate > $filters['end']) {
                return false;
            }
        }
        
        // Status filter (matching get_immunization_view.php logic)
        if ($statusFilter !== 'all') {
            $status = strtolower((string)($r['status'] ?? ''));
            $due = (string)($r['batch_schedule_date'] ?? ($r['schedule_date'] ?? ''));
            
            if ($statusFilter === 'scheduled') {
                // Show only scheduled items
                if ($status !== 'scheduled') return false;
            } else if ($statusFilter === 'upcoming') {
                // Show only scheduled items due today or in the future
                if ($status !== 'scheduled') return false;
                if ($due !== '' && $due < $todayStr) return false;
            } else if ($statusFilter === 'missed') {
                // Show missed items (past due date, not taken)
                if ($due === '' || $due >= $todayStr || $status === 'taken') return false;
            } else if ($statusFilter === 'completed') {
                // Show completed items
                if ($status !== 'taken') return false;
            }
        }
        
        return true;
    }));

    // Get unique baby_ids from filtered immunizations
    $babyIds = array_values(array_unique(array_map(function($r) { return $r['baby_id']; }, $immsFiltered)));
    
    // Fetch children for those baby_ids
    $childrenByBaby = [];
    if (!empty($babyIds)) {
        $children = supabaseSelect(
            'child_health_records',
            'baby_id,child_fname,child_lname,address,status',
            ['status' => 'accepted', 'baby_id' => $babyIds]
        ) ?: [];
        foreach ($children as $child) {
            $childrenByBaby[$child['baby_id']] = $child;
        }
    }

    // Debug counters (will be updated after items are built)
    $debug = [
        'immunizations_total' => count($immunizations),
        'immunizations_after_filters' => count($immsFiltered),
        'children_found' => count($childrenByBaby),
        'date_range' => $filters,
        'filters_applied' => [
            'vaccine' => $vaccine,
            'status' => $statusFilter
        ],
        // Legacy structure for frontend compatibility
        'children_count' => count($childrenByBaby),
        'baby_ids_count' => count($babyIds),
        'immunizations_count' => count($immunizations),
        'filter_stats' => [
            'before_filters' => count($immunizations),
            'after_vaccine_status_date' => count($immsFiltered),
            'no_operational_date' => 0
        ]
    ];

    $items = [];
    $byVaccine = [];
    $byMonth = [];

    foreach ($immsFiltered as $imm) {
        $babyId = $imm['baby_id'];
        $child = $childrenByBaby[$babyId] ?? null;
        if (!$child) {
            continue; // Skip if child is not accepted
        }

        $guidelineDate = $imm['schedule_date'] ?? null;
        $batchDate = $imm['batch_schedule_date'] ?? null;
        $catchUpDate = $imm['catch_up_date'] ?? null;
        $operationalDate = $batchDate ?: $guidelineDate ?: $catchUpDate;
        $status = strtolower($imm['status'] ?? '');

        $items[] = [
            'record_id' => $imm['id'],
            'baby_id' => $babyId,
            'child_name' => trim(($child['child_fname'] ?? '') . ' ' . ($child['child_lname'] ?? '')),
            'address' => $child['address'] ?? '',
            'vaccine_name' => $imm['vaccine_name'],
            'dose_number' => $imm['dose_number'],
            'status' => $status,
            'guideline_date' => $guidelineDate,
            'batch_schedule_date' => $batchDate,
            'catch_up_date' => $catchUpDate,
            'operational_date' => $operationalDate
        ];

        $vacKey = $imm['vaccine_name'] ?? 'Unknown';
        $byVaccine[$vacKey] = ($byVaccine[$vacKey] ?? 0) + 1;

        $monthKey = date('Y-m', strtotime($operationalDate));
        $byMonth[$monthKey] = ($byMonth[$monthKey] ?? 0) + 1;
    }

    usort($items, function ($a, $b) {
        return strcmp($a['operational_date'], $b['operational_date']);
    });

    // Update debug with final counts (before pagination)
    $totalItems = count($items);
    $debug['items_final'] = $totalItems;
    $debug['filter_stats']['after_children_join'] = $totalItems;

    // Apply pagination
    $skip = ($page - 1) * $limit;
    $has_more = $totalItems > ($skip + $limit);
    $pagedItems = array_slice($items, $skip, $limit);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'items' => $pagedItems,
            'stats' => [
                'total' => $totalItems,
                'by_vaccine' => $byVaccine,
                'by_month' => $byMonth
            ],
            'debug' => $debug,
            'total' => $totalItems,
            'page' => $page,
            'limit' => $limit,
            'has_more' => $has_more
        ]
    ]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

function buildDateRange($start, $end)
{
    $startDate = null;
    $endDate = null;

    // Start/end dates
    if ($start && trim($start) !== '') {
        $startDate = $start;
    }
    if ($end && trim($end) !== '') {
        $endDate = $end;
    }

    return ['start' => $startDate, 'end' => $endDate];
}

