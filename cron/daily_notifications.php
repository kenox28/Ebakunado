<?php
/**
 * Daily Cron Job for Sending Vaccination Schedule Notifications
 * 
 * This script automatically runs daily at 2:40 AM Philippines time to check 
 * for upcoming vaccination schedules and send SMS + Email notifications to parents.
 * 
 * Setup cron job:
 * 40 2 * * * /usr/bin/php /path/to/ebakunado/cron/daily_notifications.php
 * 
 * This will run every day at 2:40 AM Philippines time
 */

// Set timezone
date_default_timezone_set('Asia/Manila');

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log file for cron job
$logFile = __DIR__ . '/../logs/daily_notifications_' . date('Y-m-d') . '.log';

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    
    // Ensure logs directory exists
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    echo $logEntry; // Also output to console
}

// Start logging
logMessage("=== Daily Notification Cron Job Started ===");

try {
    // Include the notification service
    require_once __DIR__ . '/../php/supabase/bhw/send_schedule_notifications.php';
    
    logMessage("Daily notification service completed successfully");
    
} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
    exit(1);
}

logMessage("=== Daily Notification Cron Job Completed ===");
?>
