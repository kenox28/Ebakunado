<?php
// Ebakunado - Main Entry Point
// This serves as the main entry point for the web application

// Set error reporting for development (change to 0 for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Manila');

// Simple routing based on the request
$request_uri = $_SERVER['REQUEST_URI'];

// Remove query string and decode URI
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/index.php', '', $path);

// Route to different sections
if (empty($path) || $path === '/') {
    // Serve landing page
    include 'views/landing_page.php';
} elseif (strpos($path, '/login') === 0) {
    include 'views/login.php';
} elseif (strpos($path, '/create-account') === 0) {
    include 'views/create-account.php';
} elseif (strpos($path, '/api/') === 0) {
    // API routes - let them handle themselves
    $api_file = '.' . $path;
    if (file_exists($api_file)) {
        include $api_file;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found']);
    }
} elseif (strpos($path, '/php/') === 0) {
    // PHP backend routes - let them handle themselves
    $php_file = '.' . $path;
    if (file_exists($php_file)) {
        include $php_file;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'PHP endpoint not found']);
    }
} else {
    // Check for static files
    $file_path = '.' . $path;
    if (file_exists($file_path) && !is_dir($file_path)) {
        // Serve static files
        $mime_type = mime_content_type($file_path);
        header('Content-Type: ' . $mime_type);
        readfile($file_path);
    } else {
        // 404 page
        http_response_code(404);
        include 'views/404.php';
    }
}
?>
