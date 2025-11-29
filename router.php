<?php

require_once __DIR__ . '/vendor/autoload.php';

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;

class Router {
    private $dispatcher;
    private $basePath = '';
    
    public function __construct() {
        $this->basePath = $this->detectBasePath();

        $this->dispatcher = \FastRoute\simpleDispatcher(function(RouteCollector $r) {
            $this->defineRoutes($r);
        });
    }

    /**
     * Detect the base path of the application from the current script.
     * This allows the router to work both on localhost/ebakunado and on ebakunado.com.
     */
    private function detectBasePath(): string {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = str_replace('\\', '/', dirname($scriptName));
        if ($dir === '/' || $dir === '\\' || $dir === '.') {
            return '';
        }
        // Ensure leading slash, no trailing slash
        return '/' . trim($dir, '/');
    }
    
    private function defineRoutes(RouteCollector $r) {
        // Landing page
        $r->addRoute('GET', '/', 'views/landing-page/landing-page.html');
        $r->addRoute('GET', '/home', 'views/landing-page/landing-page.html');
        $r->addRoute('GET', '/contact', 'views/landing-page/contact-page.html');
        
        // Authentication routes
        $r->addRoute('GET', '/login', 'views/auth/login.php');
        $r->addRoute('POST', '/login', 'php/supabase/login.php');
        $r->addRoute('GET', '/register', 'views/create-account.php');
        $r->addRoute('POST', '/register', 'php/supabase/create_account.php');
        $r->addRoute('GET', '/logout', 'views/logout.php');
        
        // Admin routes (simplified - no /admin prefix)
        // $r->addRoute('GET', '/admin-dashboard', 'views/admin/dashboard.php');
        // $r->addRoute('GET', '/admin-users', 'views/admin/user-management.php');
        // $r->addRoute('GET', '/admin-bhw', 'views/admin/bhw-management.php');
        // $r->addRoute('GET', '/admin-midwives', 'views/admin/midwife-management.php');
        // $r->addRoute('GET', '/admin-locations', 'views/admin/location-management.php');
        // $r->addRoute('GET', '/admin-activity-logs', 'views/admin/activity-logs.php');
        
        // Health Worker routes (BHW and Midwife share the same bhw-page directory)
        // Using /health- prefix to differentiate from user routes
        $r->addRoute('GET', '/health-dashboard', 'views/bhw-page/dashboard.php');
        $r->addRoute('GET', '/health-children', 'views/bhw-page/child-health-list.php');
        $r->addRoute('GET', '/health-immunizations', 'views/bhw-page/immunization.php');
        $r->addRoute('GET', '/health-pending', 'views/bhw-page/pending-approval.php');
        $r->addRoute('GET', '/health-add-child', 'views/bhw-page/add-child.php');
        $r->addRoute('GET', '/health-target-client', 'views/bhw-page/target-client-list.php');
        $r->addRoute('GET', '/health-profile', 'views/bhw-page/profile-management.php');
        $r->addRoute('GET', '/health-child/{id}', 'views/bhw-page/child-health-record.php');
        $r->addRoute('GET', '/health-vaccination-planner', 'views/bhw-page/vaccination-planner.php');
        $r->addRoute('GET', '/health-babycard-requests', 'views/bhw-page/babycard-doc-requests.php');
        $r->addRoute('GET', '/health-system-settings', 'views/bhw-page/system-settings.php');
        $r->addRoute('GET', '/health-chr-doc-requests', 'views/bhw-page/chr-doc-requests.php');
        
        // Legacy routes for backward compatibility (redirect to new /health- routes)
        $r->addRoute('GET', '/bhw-dashboard', 'views/bhw-page/dashboard.php');
        $r->addRoute('GET', '/midwife-dashboard', 'views/bhw-page/dashboard.php');
        
        // User routes (simplified - no /user prefix)
        // Newer user pages live under views/user-page
        $r->addRoute('GET', '/dashboard', 'views/user-page/dashboard.php');
        $r->addRoute('GET', '/children', 'views/user-page/children-list.php');
        $r->addRoute('GET', '/immunizations', 'views/user-page/child-health-record.php');
        $r->addRoute('GET', '/upcoming', 'views/user-page/upcoming-schedule.php');
        $r->addRoute('GET', '/upcoming/{baby_id}', 'views/user-page/upcoming-schedule.php');
        $r->addRoute('GET', '/approved-requests', 'views/user-page/approved-requests.php');
        $r->addRoute('GET', '/add-child', 'views/user-page/add-child-request.php');
        $r->addRoute('GET', '/profile', 'views/user-page/profile-management.php');

        // Legacy user routes kept for backward compatibility (old views/users pages)
        $r->addRoute('GET', '/missed', 'views/users/missed_immunization.php');
        $r->addRoute('GET', '/child/{id}', 'views/users/view_child.php');
        
        // Super Admin routes (using superadmin-page directory)
        // Kept for backward compatibility
        // $r->addRoute('GET', '/superadmin-dashboard', 'views/superadmin-page/dashboard.php');
        // $r->addRoute('GET', '/superadmin-admins', 'views/superadmin-page/admin-management.php');
        // $r->addRoute('GET', '/superadmin-bhw', 'views/superadmin-page/bhw-management.php');
        // $r->addRoute('GET', '/superadmin-midwives', 'views/superadmin-page/midwife-management.php');
        // $r->addRoute('GET', '/superadmin-locations', 'views/superadmin-page/location-management.php');
        // $r->addRoute('GET', '/superadmin-users', 'views/superadmin-page/user-management.php');
        // $r->addRoute('GET', '/superadmin-activity-logs', 'views/superadmin-page/activity-logs.php');
        // $r->addRoute('GET', '/superadmin-privacy-consents', 'views/superadmin-page/privacy-consents.php');
        // $r->addRoute('GET', '/superadmin-system-settings', 'views/superadmin-page/system-settings.php');

        // Admin routes (alias to superadmin-page, but shorter URLs)
        $r->addRoute('GET', '/admin-dashboard', 'views/superadmin-page/dashboard.php');
        $r->addRoute('GET', '/admin-users', 'views/superadmin-page/user-management.php');
        $r->addRoute('GET', '/admin-bhw', 'views/superadmin-page/bhw-management.php');
        $r->addRoute('GET', '/admin-midwives', 'views/superadmin-page/midwife-management.php');
        $r->addRoute('GET', '/admin-locations', 'views/superadmin-page/location-management.php');
        $r->addRoute('GET', '/admin-activity-logs', 'views/superadmin-page/activity-logs.php');
        $r->addRoute('GET', '/admin-privacy-consents', 'views/superadmin-page/privacy-consents.php');
        $r->addRoute('GET', '/admin-system-settings', 'views/superadmin-page/system-settings.php');
        
        // API routes (if needed for AJAX calls)
        $r->addRoute('GET', '/api/dashboard-stats', 'php/supabase/bhw/get_dashboard_stats.php');
        $r->addRoute('GET', '/api/activity-logs', 'api/get_activity_logs.php');
        $r->addRoute('GET', '/api/users', 'api/get_users.php');
        $r->addRoute('GET', '/api/bhw', 'api/get_bhw.php');
        $r->addRoute('GET', '/api/midwives', 'api/get_midwives.php');
        $r->addRoute('GET', '/api/locations', 'api/get_locations.php');
        
        // Notification API routes
        $r->addRoute('GET', '/api/bhw-notifications', 'php/supabase/shared/get_bhw_notifications_simple.php');
        $r->addRoute('GET', '/api/user-notifications', 'php/supabase/users/get_user_notifications.php');
        $r->addRoute('GET', '/api/midwife-notifications', 'php/supabase/shared/get_bhw_notifications_simple.php');
        $r->addRoute('POST', '/api/mark-notification-read', 'php/supabase/bhw/mark_notification_read.php');
        $r->addRoute('POST', '/api/mark-notifications-read-all', 'php/supabase/bhw/mark_notifications_read_all.php');
    }
    
    public function dispatch() {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Remove query string from URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Remove base path if your app is in a subdirectory (e.g. /ebakunado)
        if (!empty($this->basePath) && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        
        $uri = rawurldecode($uri);
        
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $this->handleNotFound();
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $this->handleMethodNotAllowed($allowedMethods);
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $this->handleFound($handler, $vars);
                break;
        }
    }
    
    private function handleFound($handler, $vars = []) {
        // Extract parameters and add them to $_GET for backward compatibility
        foreach ($vars as $key => $value) {
            $_GET[$key] = $value;
        }
        
        // Handle different types of handlers
        if (is_string($handler)) {
            // File path handler
            if (file_exists($handler)) {
                $extension = pathinfo($handler, PATHINFO_EXTENSION);
                
                if ($extension === 'php') {
                    include $handler;
                } elseif ($extension === 'html') {
                    // Serve HTML files directly
                    $content = file_get_contents($handler);
                    echo $content;
                } else {
                    // For other files, redirect to them, respecting the base path
                    $prefix = $this->basePath ?: '';
                    header('Location: ' . $prefix . '/' . ltrim($handler, '/'));
                    exit;
                }
            } else {
                $this->handleNotFound();
            }
        } elseif (is_callable($handler)) {
            // Callable handler
            call_user_func_array($handler, $vars);
        }
    }
    
    private function handleNotFound() {
        http_response_code(404);

        // Prefer a dedicated 404 view if available
        $view404 = __DIR__ . '/views/404.php';
        if (file_exists($view404)) {
            include $view404;
            return;
        }

        // Fallback simple HTML if the view does not exist
        $homeHref = (!empty($this->basePath) ? $this->basePath : '') . '/';
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>404 - Page Not Found</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                h1 { color: #e74c3c; }
                p { color: #666; }
                a { color: #3498db; text-decoration: none; }
                a:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <h1>404 - Page Not Found</h1>
            <p>The page you are looking for could not be found.</p>
            <a href="' . htmlspecialchars($homeHref, ENT_QUOTES, 'UTF-8') . '">← Back to Home</a>
        </body>
        </html>';
    }
    
    private function handleMethodNotAllowed($allowedMethods) {
        http_response_code(405);
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>405 - Method Not Allowed</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                h1 { color: #e74c3c; }
                p { color: #666; }
            </style>
        </head>
        <body>
            <h1>405 - Method Not Allowed</h1>
            <p>This method is not allowed for this route.</p>
            <p>Allowed methods: ' . implode(', ', $allowedMethods) . '</p>
        </body>
        </html>';
    }
}
