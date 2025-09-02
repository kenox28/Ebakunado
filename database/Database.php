<?php
$localhost = "localhost";
$username = "root";
$password = "";
$database = "ebakunado_db";

// Connect to database
// Disable error reporting for the client
error_reporting(0);
ini_set('display_errors', 0);

// Set error handler to throw exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // First connect without database to create it if it doesn't exist
    $connect = mysqli_connect($localhost, $username, $password);
    
    if (!$connect) {
        throw new Exception("Failed to connect to MySQL server");
    }
    
    // Create database if it doesn't exist
    $create_db = "CREATE DATABASE IF NOT EXISTS $database";
    if (!mysqli_query($connect, $create_db)) {
        throw new Exception("Failed to create database: " . mysqli_error($connect));
    }
    
    // Select the database
    if (!mysqli_select_db($connect, $database)) {
        throw new Exception("Failed to select database: " . mysqli_error($connect));
    }
    
} catch (Exception $e) {
    // Log error instead of outputting JSON (which can cause issues)
    error_log("Database connection failed: " . $e->getMessage());
    $connect = false;
}

// Table definitions - these are just strings, not executed yet
$users = "CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255),
    fname VARCHAR(50),
    lname VARCHAR(50),
    email VARCHAR(50),
    passw VARCHAR(255),
    phone_number VARCHAR(20),
    salt VARCHAR(64),
    profileImg VARCHAR(255),
    failed_attempts INT DEFAULT 0,
    lockout_time DATETIME DEFAULT NULL,
    gender VARCHAR(255),
    bdate VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    role VARCHAR(255) DEFAULT 'user'
)";

$midwives = "CREATE TABLE IF NOT EXISTS midwives (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    midwife_id VARCHAR(255),
    fname VARCHAR(50),
    lname VARCHAR(50),
    email VARCHAR(50),
    pass VARCHAR(50),
    profileImg VARCHAR(255),
    gender VARCHAR(255),
    bdate VARCHAR(255),
    license_number VARCHAR(255),
    specialization VARCHAR(255),
    permissions VARCHAR(255) DEFAULT 'view',
    Approve BOOLEAN DEFAULT 0,
    last_active DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$bhw = "CREATE TABLE IF NOT EXISTS bhw (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bhw_id VARCHAR(255),
    fname VARCHAR(50),
    lname VARCHAR(50),
    email VARCHAR(50),
    pass VARCHAR(50),
    profileImg VARCHAR(255),
    gender VARCHAR(255),
    bdate VARCHAR(255),
    barangay VARCHAR(255),
    permissions VARCHAR(255) DEFAULT 'view',
    last_active DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";



$immunization_records = "CREATE TABLE IF NOT EXISTS immunization_records(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(50),
    fname VARCHAR(50),
    lname VARCHAR(50),
    age INT,
    address VARCHAR(255),
    vaccine_name VARCHAR(100),
    dose_number INT,
    date_given DATE,
    next_due_date DATE,
    batch_number VARCHAR(50),
    provider_id VARCHAR(255),
    status VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$admin = "CREATE TABLE IF NOT EXISTS admin (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    admin_id VARCHAR(255) NOT NULL UNIQUE,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    profileImg VARCHAR(255) DEFAULT 'noprofile.png',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$super_admin = "CREATE TABLE IF NOT EXISTS super_admin (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    super_admin_id VARCHAR(255) NOT NULL UNIQUE,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$activity_logs = "CREATE TABLE IF NOT EXISTS activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255),
    user_type ENUM('admin', 'super_admin', 'midwife', 'bhw', 'user') NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Function to initialize database tables
function initializeDatabase($connect) {
    if (!$connect) {
        return false;
    }
    
    // Create all tables
    mysqli_query($connect, $GLOBALS['users']);
    mysqli_query($connect, $GLOBALS['midwives']);
    mysqli_query($connect, $GLOBALS['bhw']);
    mysqli_query($connect, $GLOBALS['immunization_records']);
    mysqli_query($connect, $GLOBALS['admin']);
    mysqli_query($connect, $GLOBALS['super_admin']);
    mysqli_query($connect, $GLOBALS['activity_logs']);

    // Check if the default admin already exists
    $check_admin = "SELECT * FROM admin";
    $result = mysqli_query($connect, $check_admin);

    if ($result && mysqli_num_rows($result) === 0) {
        // Insert default admin if it doesn't exist
        $default_admin = "INSERT INTO admin (admin_id, fname, lname, email, pass) VALUES
        ('ADM001', 'Default', 'Admin', 'admin@gmail.com', '" . md5("admin123456") . "'),
        ('ADM002', 'Default', 'Admin', 'iquenxzx@gmail.com', '" . md5("iquen123456") . "'),
        ('ADM003', 'Default', 'Admin', 'russeljhondasigan@gmail.com', '" . md5("russel123456") . "')";
        mysqli_query($connect, $default_admin);
    }

    // Check if the default super admin already exists
    $check_super_admin = "SELECT * FROM super_admin";
    $result = mysqli_query($connect, $check_super_admin);

    if ($result && mysqli_num_rows($result) === 0) {
        // Insert default super admin if it doesn't exist
        $default_super_admin = "INSERT INTO super_admin (super_admin_id, fname, lname, email, pass) VALUES
        ('SADM001', 'Super', 'Admin', 'superadmin@gmail.com', '" . md5("superadmin123456") . "')";
        mysqli_query($connect, $default_super_admin);
    }
    
    return true;
}
?> 