<?php
// Suppress all PHP warnings and errors to ensure clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

// Check if BHW is logged in
if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - BHW ID not found in session']);
    exit();
}

$bhw_id = $_SESSION['bhw_id'];

try {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    // 1. Pending Child Health Record Approvals
    $pending_approvals = supabaseSelect('child_health_records', 'id,baby_id,child_fname,child_lname,date_created', ['status' => 'pending'], 'date_created.desc', 10);
    $pending_count = $pending_approvals ? count($pending_approvals) : 0;

    // 2. Today's Vaccination Schedules
    $today_schedules = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,dose_number,schedule_date,status', 
        ['schedule_date' => $today], 
        null, 50
    );
    
    // Filter out completed ones
    if ($today_schedules) {
        $today_schedules = array_filter($today_schedules, function($schedule) {
            return $schedule['status'] !== 'completed' && $schedule['status'] !== 'taken';
        });
    }
    $today_count = $today_schedules ? count($today_schedules) : 0;

    // 3. Missed Vaccination Schedules (before today, not completed)
    $missed_schedules = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,dose_number,schedule_date,status', 
        ['schedule_date.lt' => $today], 
        null, 50
    );
    
    // Filter out completed ones
    if ($missed_schedules) {
        $missed_schedules = array_filter($missed_schedules, function($schedule) {
            return $schedule['status'] !== 'completed' && $schedule['status'] !== 'taken';
        });
    }
    $missed_count = $missed_schedules ? count($missed_schedules) : 0;

    // 4. Total Children Under Care (accepted child health records)
    $total_children = supabaseSelect('child_health_records', 'baby_id', ['status' => 'accepted'], null, 1000);
    $total_children_count = $total_children ? count($total_children) : 0;

    // 5. Tomorrow's Vaccination Schedules
    $tomorrow_schedules = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,dose_number,schedule_date,status', 
        ['schedule_date' => $tomorrow], 
        null, 20
    );
    
    // Filter out completed ones
    if ($tomorrow_schedules) {
        $tomorrow_schedules = array_filter($tomorrow_schedules, function($schedule) {
            return $schedule['status'] !== 'completed' && $schedule['status'] !== 'taken';
        });
    }
    $tomorrow_count = $tomorrow_schedules ? count($tomorrow_schedules) : 0;

    // 6. Recent Activity (last 7 days)
    $last_week = date('Y-m-d H:i:s', strtotime('-7 days'));
    $recent_activities = [];
    
    // Recent child health record submissions
    $recent_chr = supabaseSelect('child_health_records', 
        'id,child_fname,child_lname,status,date_created,date_updated', 
        ['date_created.gte' => $last_week], 
        'date_created.desc', 10
    );
    
    if ($recent_chr) {
        foreach ($recent_chr as $chr) {
            $activity_type = 'submission';
            $title = 'New Child Record Submitted';
            $description = 'Child registration for ' . $chr['child_fname'] . ' ' . $chr['child_lname'];
            $icon = '📋';
            
            if ($chr['status'] === 'accepted') {
                $activity_type = 'approval';
                $title = 'Child Record Approved';
                $description = 'Child registration for ' . $chr['child_fname'] . ' ' . $chr['child_lname'] . ' approved';
                $icon = '✅';
            } elseif ($chr['status'] === 'rejected') {
                $activity_type = 'rejection';
                $title = 'Child Record Rejected';
                $description = 'Child registration for ' . $chr['child_fname'] . ' ' . $chr['child_lname'] . ' rejected';
                $icon = '❌';
            }

            $recent_activities[] = [
                'type' => $activity_type,
                'title' => $title,
                'description' => $description,
                'icon' => $icon,
                'timestamp' => $chr['date_created'],
                'status' => $chr['status']
            ];
        }
    }

    // Recent vaccination completions
    $recent_vaccinations = supabaseSelect('immunization_records', 
        'id,baby_id,vaccine_name,status,date_given', 
        ['date_given.gte' => $last_week, 'status' => 'completed'], 
        'date_given.desc', 5
    );
    
    if ($recent_vaccinations) {
        foreach ($recent_vaccinations as $vaccination) {
            // Get child info
            $child_info = supabaseSelect('child_health_records', 'child_fname,child_lname', ['baby_id' => $vaccination['baby_id']], null, 1);
            $child_name = 'Unknown Child';
            if ($child_info && count($child_info) > 0) {
                $child_name = $child_info[0]['child_fname'] . ' ' . $child_info[0]['child_lname'];
            }

            $recent_activities[] = [
                'type' => 'vaccination',
                'title' => 'Vaccination Completed',
                'description' => $vaccination['vaccine_name'] . ' completed for ' . $child_name,
                'icon' => '💉',
                'timestamp' => $vaccination['date_given'],
                'status' => 'completed'
            ];
        }
    }

    // Sort activities by timestamp (most recent first)
    usort($recent_activities, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    // Limit to 8 most recent activities
    $recent_activities = array_slice($recent_activities, 0, 8);

    // 7. Quick Tasks Summary
    $overdue_count = 0;
    if ($missed_schedules) {
        $overdue_count = count($missed_schedules);
    }

    // 8. Monthly Vaccine Counts
    function getMonthlyVaccineCounts($targetMonth) {
        // Get first and last day of the month
        $firstDay = date('Y-m-01', strtotime($targetMonth . '-01'));
        $lastDay = date('Y-m-t', strtotime($firstDay));
        
        // All 14 vaccines in order
        $allVaccines = [
            'BCG',
            'Hepatitis B',
            'Pentavalent (DPT-HepB-Hib) - 1st',
            'OPV - 1st',
            'PCV - 1st',
            'Pentavalent (DPT-HepB-Hib) - 2nd',
            'OPV - 2nd',
            'PCV - 2nd',
            'Pentavalent (DPT-HepB-Hib) - 3rd',
            'OPV - 3rd',
            'IPV',
            'PCV - 3rd',
            'MCV1 (AMV)',
            'MCV2 (MMR)'
        ];
        
        // Get all accepted children
        $children = supabaseSelect('child_health_records', 'baby_id', ['status' => 'accepted']) ?: [];
        $babyIds = array_column($children, 'baby_id');
        
        if (empty($babyIds)) {
            // Return all vaccines with 0 count
            $result = [];
            foreach ($allVaccines as $vaccine) {
                $result[] = ['name' => $vaccine, 'count' => 0];
            }
            return $result;
        }
        
        // Fetch immunization records for the month
        $immunizations = [];
        $batchSize = 200;
        $offset = 0;
        while (true) {
            $batch = supabaseSelect(
                'immunization_records',
                'id,baby_id,vaccine_name,schedule_date,catch_up_date,status',
                ['baby_id' => $babyIds],
                'schedule_date.asc',
                $batchSize,
                $offset
            );
            if (!$batch || count($batch) === 0) break;
            $immunizations = array_merge($immunizations, $batch);
            if (count($batch) < $batchSize) break;
            $offset += $batchSize;
        }
        
        // Filter by month and status
        // Use catch_up_date if missed, otherwise use schedule_date (for procurement analysis)
        // Include both 'scheduled' and 'missed' statuses (both need vaccines)
        // Exclude 'taken' and 'completed' (already given)
        $monthRecords = array_filter($immunizations, function($r) use ($firstDay, $lastDay) {
            $status = strtolower($r['status'] ?? '');
            
            // Exclude already completed/taken vaccines
            if ($status === 'taken' || $status === 'completed') {
                return false;
            }
            
            // Only include scheduled or missed vaccines (both need to be given)
            if ($status !== 'scheduled' && $status !== 'missed') {
                return false;
            }
            
            // Determine which date to use for month calculation
            // If missed and has catch_up_date, use catch_up_date
            // Otherwise, use schedule_date (original guideline date)
            $targetDate = '';
            if ($status === 'missed' && !empty($r['catch_up_date'])) {
                $targetDate = $r['catch_up_date'] ?? '';
            } else {
                $targetDate = $r['schedule_date'] ?? '';
            }
            
            // Check if target date falls within the target month
            return $targetDate >= $firstDay && $targetDate <= $lastDay;
        });
        
        // Count by vaccine
        $counts = [];
        foreach ($monthRecords as $record) {
            $vaccineName = $record['vaccine_name'] ?? '';
            if ($vaccineName) {
                $counts[$vaccineName] = ($counts[$vaccineName] ?? 0) + 1;
            }
        }
        
        // Build result with all 14 vaccines
        $result = [];
        foreach ($allVaccines as $vaccine) {
            $result[] = [
                'name' => $vaccine,
                'count' => $counts[$vaccine] ?? 0
            ];
        }
        
        return $result;
    }
    
    $currentMonth = date('Y-m');
    $nextMonth = date('Y-m', strtotime('+1 month'));
    $currentMonthName = date('F Y');
    $nextMonthName = date('F Y', strtotime('+1 month'));
    
    $currentMonthVaccines = getMonthlyVaccineCounts($currentMonth);
    $nextMonthVaccines = getMonthlyVaccineCounts($nextMonth);
    
    $currentMonthTotal = array_sum(array_column($currentMonthVaccines, 'count'));
    $nextMonthTotal = array_sum(array_column($nextMonthVaccines, 'count'));

    // Set proper JSON header
    header('Content-Type: application/json');

    echo json_encode([
        'status' => 'success',
        'data' => [
            'stats' => [
                'pending_approvals' => $pending_count,
                'today_vaccinations' => $today_count,
                'missed_vaccinations' => $missed_count,
                'total_children' => $total_children_count,
                'tomorrow_vaccinations' => $tomorrow_count,
                'overdue_tasks' => $overdue_count
            ],
            'recent_activities' => $recent_activities,
            'pending_approvals_list' => $pending_approvals ? array_slice($pending_approvals, 0, 5) : [],
            'today_schedules_list' => $today_schedules ? array_slice($today_schedules, 0, 5) : [],
            'tomorrow_schedules_list' => $tomorrow_schedules ? array_slice($tomorrow_schedules, 0, 5) : [],
            'monthly_vaccines' => [
                'current_month' => [
                    'month' => $currentMonth,
                    'month_name' => $currentMonthName,
                    'total' => $currentMonthTotal,
                    'vaccines' => $currentMonthVaccines
                ],
                'next_month' => [
                    'month' => $nextMonth,
                    'month_name' => $nextMonthName,
                    'total' => $nextMonthTotal,
                    'vaccines' => $nextMonthVaccines
                ]
            ]
        ]
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch dashboard data: ' . $e->getMessage()
    ]);
}
?>
