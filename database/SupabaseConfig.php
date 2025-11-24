<?php
// Supabase Configuration for Ebakunado
// Replace Database.php with this file

// Set Philippines timezone for all Supabase operations
date_default_timezone_set('Asia/Manila');
ini_set('date.timezone', 'Asia/Manila');

// Supabase Configuration
$supabase_url = "https://wdwjddwrkxvipzabroed.supabase.co"; // Replace with your Supabase project URL
$supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Indkd2pkZHdya3h2aXB6YWJyb2VkIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1ODA4MjkwNSwiZXhwIjoyMDczNjU4OTA1fQ.w3PdR-eP8WVK-H6l2sc9wjdo4ORx_J12Nd7DvMOV9_E"; // Replace with your Supabase anon key
$supabase_service_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Indkd2pkZHdya3h2aXB6YWJyb2VkIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1ODA4MjkwNSwiZXhwIjoyMDczNjU4OTA1fQ.w3PdR-eP8WVK-H6l2sc9wjdo4ORx_J12Nd7DvMOV9_E"; // Replace with your service key for admin operations

// JWT Configuration
// IMPORTANT: Change this secret key in production for security!
// You can also set it as environment variable: JWT_SECRET_KEY
$jwt_secret_key = getenv('JWT_SECRET_KEY') ?: 'ebakunado_jwt_secret_key_change_in_production_2025';

// Disable error reporting for the client
error_reporting(0);
ini_set('display_errors', 0);

// Global connection variable (for compatibility with existing code)
$connect = true; // We'll use this to maintain compatibility

// Supabase API Helper Class
class SupabaseDB {
    private $url;
    private $key;
    private $service_key;
    private $last_error = null;
    private $last_status = null;
    
    public function __construct($url, $key, $service_key = null) {
        $this->url = rtrim($url, '/');
        $this->key = $key;
        $this->service_key = $service_key ?: $key;
    }
    
    // Make API request to Supabase
    private function makeRequest($endpoint, $method = 'GET', $data = null, $use_service_key = false) {
        $url = $this->url . '/rest/v1/' . $endpoint;
        $key = $use_service_key ? $this->service_key : $this->key;
        
        $headers = [
            'apikey: ' . $key,
            'Authorization: Bearer ' . $key,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code >= 400) {
            error_log("Supabase API Error: HTTP $http_code - $response");
            $this->last_error = $response;
            $this->last_status = $http_code;
            return false;
        }
        
        $this->last_error = null;
        $this->last_status = $http_code;
        return json_decode($response, true);
    }
    
    // SELECT query with WHERE conditions
    public function select($table, $columns = '*', $conditions = [], $orderBy = null, $limit = null, $offset = null) {
        $endpoint = $table . '?';
        
        // Add column selection
        if ($columns !== '*') {
            $endpoint .= 'select=' . $columns . '&';
        }
        
        // Add WHERE conditions (URL-encode values, no quotes per PostgREST)
        foreach ($conditions as $column => $value) {
            if (is_array($value) && isset($value['operator']) && array_key_exists('value', $value)) {
                $operator = strtolower(trim($value['operator']));
                $conditionValue = $value['value'];

                if (is_array($conditionValue)) {
                    $encoded = array_map(function($v) {
                        return rawurlencode((string)$v);
                    }, $conditionValue);
                    $endpoint .= $column . '=' . $operator . '.(' . implode(',', $encoded) . ')&';
                } else {
                    $endpoint .= $column . '=' . $operator . '.' . rawurlencode((string)$conditionValue) . '&';
                }
            } elseif (is_array($value)) {
                // Handle IN conditions
                $encoded = array_map(function($v) {
                    return rawurlencode((string)$v);
                }, $value);
                $endpoint .= $column . '=in.(' . implode(',', $encoded) . ')&';
            } else {
                $endpoint .= $column . '=eq.' . rawurlencode((string)$value) . '&';
            }
        }
        
        // Add ORDER BY
        if ($orderBy) {
            $endpoint .= 'order=' . $orderBy . '&';
        }
        
        // Add LIMIT/OFFSET
        if ($limit) { $endpoint .= 'limit=' . (int)$limit . '&'; }
        if ($offset) { $endpoint .= 'offset=' . (int)$offset . '&'; }
        
        $endpoint = rtrim($endpoint, '&');
        
        return $this->makeRequest($endpoint, 'GET', null, true);
    }
    
    // INSERT data
    public function insert($table, $data) {
        $endpoint = $table;
        return $this->makeRequest($endpoint, 'POST', $data, true);
    }
    
    // UPDATE data
    public function update($table, $data, $conditions) {
        $endpoint = $table . '?';
        
        // Add WHERE conditions (URL-encode values)
        foreach ($conditions as $column => $value) {
            $endpoint .= $column . '=eq.' . rawurlencode((string)$value) . '&';
        }
        
        $endpoint = rtrim($endpoint, '&');
        
        return $this->makeRequest($endpoint, 'PATCH', $data, true);
    }
    
    // DELETE data
    public function delete($table, $conditions) {
        $endpoint = $table . '?';
        
        // Add WHERE conditions (URL-encode values)
        foreach ($conditions as $column => $value) {
            $endpoint .= $column . '=eq.' . rawurlencode((string)$value) . '&';
        }
        
        $endpoint = rtrim($endpoint, '&');
        
        return $this->makeRequest($endpoint, 'DELETE', null, true);
    }

    public function getLastError() {
        return $this->last_error;
    }

    public function getLastStatus() {
        return $this->last_status;
    }
    
    // Execute raw SQL (for complex queries)
    public function query($sql) {
        // For complex queries, we'll need to use Supabase's RPC functions
        // This is a simplified version - you might need to create stored procedures
        error_log("Raw SQL queries not directly supported. Use specific methods instead.");
        return false;
    }
    
    // Prepare statement equivalent (for security)
    public function prepare($sql) {
        // Return a prepared statement object
        return new SupabasePreparedStatement($this, $sql);
    }
    
    // Get last insert ID (Supabase returns the inserted record)
    public function insertId($result) {
        if ($result && is_array($result) && count($result) > 0) {
            return $result[0]['id'] ?? null;
        }
        return null;
    }
    
    // Get number of affected rows
    public function affectedRows($result) {
        if ($result && is_array($result)) {
            return count($result);
        }
        return 0;
    }
    
    // Check if result has rows
    public function numRows($result) {
        if ($result && is_array($result)) {
            return count($result);
        }
        return 0;
    }
    
    // Fetch associative array
    public function fetchAssoc($result) {
        if ($result && is_array($result) && count($result) > 0) {
            return $result[0];
        }
        return false;
    }
    
    // Fetch all rows
    public function fetchAll($result) {
        return $result ?: [];
    }
}

// Prepared Statement Class
class SupabasePreparedStatement {
    private $db;
    private $sql;
    private $params = [];
    
    public function __construct($db, $sql) {
        $this->db = $db;
        $this->sql = $sql;
    }
    
    public function bind_param($types, ...$params) {
        $this->params = $params;
        return true;
    }
    
    public function execute() {
        // For now, we'll handle simple prepared statements
        // Complex queries might need to be rewritten for Supabase
        return $this->db->query($this->sql);
    }
    
    public function get_result() {
        $result = $this->execute();
        return new SupabaseResult($result);
    }
    
    public function close() {
        $this->params = [];
        return true;
    }
}

// Result Class
class SupabaseResult {
    private $data;
    private $position = 0;
    
    public function __construct($data) {
        $this->data = $data ?: [];
    }
    
    public function fetch_assoc() {
        if ($this->position < count($this->data)) {
            return $this->data[$this->position++];
        }
        return false;
    }
    
    public function num_rows() {
        return count($this->data);
    }
    
    public function fetch_all() {
        return $this->data;
    }
}

// Initialize Supabase connection
try {
    $supabase = new SupabaseDB($supabase_url, $supabase_key, $supabase_service_key);
    
    // Test connection
    $test_result = $supabase->select('users', 'id', [], null, 1);
    if ($test_result === false) {
        error_log("Supabase connection failed");
        $connect = false;
    }
    
} catch (Exception $e) {
    error_log("Supabase initialization failed: " . $e->getMessage());
    $connect = false;
}

// Table definitions for Supabase (SQL to be run in Supabase SQL editor)
$table_definitions = [
    'users' => "CREATE TABLE IF NOT EXISTS public.users (
        id SERIAL PRIMARY KEY,
        user_id VARCHAR(255),
        fname VARCHAR(50),
        lname VARCHAR(50),
        email VARCHAR(50),
        passw VARCHAR(255),
        phone_number VARCHAR(20),
        salt VARCHAR(64),
        profileimg VARCHAR(255),
        failed_attempts INTEGER DEFAULT 0,
        lockout_time TIMESTAMP DEFAULT NULL,
        gender VARCHAR(255),
        place VARCHAR(255),
        created_at TIMESTAMP DEFAULT NOW(),
        updated TIMESTAMP DEFAULT NOW(),
        role VARCHAR(255) DEFAULT 'user',
        philhealth_no VARCHAR(255),
        nhts VARCHAR(255),
        family_number VARCHAR(255)
    )",
    
    'midwives' => "CREATE TABLE midwives (
        id SERIAL PRIMARY KEY,
        midwife_id VARCHAR(255),
        fname VARCHAR(50),
        lname VARCHAR(50),
        email VARCHAR(50),
        pass VARCHAR(255),
        phone_number VARCHAR(20),
        salt VARCHAR(64),
        profileImg VARCHAR(255) DEFAULT 'noprofile.png',
        gender VARCHAR(255),
        place VARCHAR(255),
        permissions VARCHAR(255) DEFAULT 'view',
        last_active TIMESTAMP DEFAULT NULL,
        created_at TIMESTAMP DEFAULT NOW(),
        updated TIMESTAMP DEFAULT NOW(),
        role VARCHAR(255)
    )",
    
    'bhw' => "CREATE TABLE bhw (
        id SERIAL PRIMARY KEY,
        bhw_id VARCHAR(255),
        fname VARCHAR(50),
        lname VARCHAR(50),
        email VARCHAR(50),
        pass VARCHAR(255),
        phone_number VARCHAR(20),
        salt VARCHAR(64),
        profileImg VARCHAR(255) DEFAULT 'noprofile.png',
        gender VARCHAR(255),
        place VARCHAR(255),
        permissions VARCHAR(255) DEFAULT 'view',
        last_active TIMESTAMP DEFAULT NULL,
        created_at TIMESTAMP DEFAULT NOW(),
        updated TIMESTAMP DEFAULT NOW(),
        role VARCHAR(255)
    )",
    
    'locations' => "CREATE TABLE locations (
        id SERIAL PRIMARY KEY,
        province VARCHAR(100) NOT NULL,
        city_municipality VARCHAR(100) NOT NULL,
        barangay VARCHAR(100) NOT NULL,
        purok VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
    )",
    
    'child_health_records' => "CREATE TABLE IF NOT EXISTS public.child_health_records (
        id SERIAL PRIMARY KEY,
        user_id VARCHAR(255) NOT NULL,
        baby_id VARCHAR(255) NOT NULL,
        child_fname VARCHAR(100) NOT NULL,
        child_lname VARCHAR(100) NOT NULL,
        child_gender VARCHAR(10),
        child_birth_date DATE NOT NULL,
        place_of_birth VARCHAR(255),
        mother_name VARCHAR(100) NOT NULL,
        father_name VARCHAR(100),
        address VARCHAR(255) NOT NULL,
        birth_weight NUMERIC(5,2),
        birth_height NUMERIC(5,2),
        birth_attendant VARCHAR(100),
        babys_card VARCHAR(500),
        delivery_type VARCHAR(100),
        birth_order VARCHAR(100),
        date_created TIMESTAMP DEFAULT NOW(),
        date_updated TIMESTAMP DEFAULT NOW(),
        status VARCHAR(50) DEFAULT 'pending',
        qr_code VARCHAR(255),
        exclusive_breastfeeding_1mo BOOLEAN DEFAULT FALSE,
        exclusive_breastfeeding_2mo BOOLEAN DEFAULT FALSE,
        exclusive_breastfeeding_3mo BOOLEAN DEFAULT FALSE,
        exclusive_breastfeeding_4mo BOOLEAN DEFAULT FALSE,
        exclusive_breastfeeding_5mo BOOLEAN DEFAULT FALSE,
        exclusive_breastfeeding_6mo BOOLEAN DEFAULT FALSE,
        complementary_feeding_6mo VARCHAR(255),
        complementary_feeding_7mo VARCHAR(255),
        complementary_feeding_8mo VARCHAR(255),
        lpm DATE,
        allergies VARCHAR(255),
        blood_type VARCHAR(10),
        family_planning VARCHAR(255),
        date_newbornscreening DATE,
        placenewbornscreening TEXT

    )",
    
    'immunization_records' => "CREATE TABLE immunization_records (
        id SERIAL PRIMARY KEY,
        baby_id VARCHAR(50),
        vaccine_name VARCHAR(100),
        dose_number INTEGER,
        weight DECIMAL(5,2),
        height DECIMAL(5,2),
        temperature DECIMAL(5,2),
        status VARCHAR(50),
        schedule_date DATE,
        batch_schedule_date DATE,
        date_given DATE,
        catch_up_date DATE,
        administered_by VARCHAR(255),
        created_at TIMESTAMP DEFAULT NOW(),
        updated TIMESTAMP DEFAULT NOW()
    )",

    'mother_tetanus_doses' => "CREATE TABLE IF NOT EXISTS public.mother_tetanus_doses (
        id SERIAL PRIMARY KEY,
        user_id VARCHAR(255),
        dose1_date DATE,
        dose2_date DATE,
        dose3_date DATE,
        dose4_date DATE,
        dose5_date DATE,
        date_created TIMESTAMP DEFAULT NOW(),
        date_updated TIMESTAMP DEFAULT NOW()
    )",
    
    'admin' => "CREATE TABLE admin (
        id SERIAL PRIMARY KEY,
        admin_id VARCHAR(255) NOT NULL UNIQUE,
        fname VARCHAR(50) NOT NULL,
        lname VARCHAR(50) NOT NULL,
        email VARCHAR(50) NOT NULL UNIQUE,
        pass VARCHAR(255) NOT NULL,
        profileImg VARCHAR(255) DEFAULT 'noprofile.png',
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
    )",
    
    'super_admin' => "CREATE TABLE super_admin (
        id SERIAL PRIMARY KEY,
        super_admin_id VARCHAR(255) NOT NULL UNIQUE,
        fname VARCHAR(50) NOT NULL,
        lname VARCHAR(50) NOT NULL,
        email VARCHAR(50) NOT NULL UNIQUE,
        pass VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
    )",
    
    'activity_logs' => "CREATE TABLE activity_logs (
        log_id SERIAL PRIMARY KEY,
        user_id VARCHAR(255),
        user_type VARCHAR(20) CHECK (user_type IN ('admin', 'super_admin', 'midwife', 'bhw', 'user')),
        action_type VARCHAR(50) NOT NULL,
        description TEXT NOT NULL,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT NOW()
    )"
];

// CREATE TABLE IF NOT EXISTS notification_logs (
//     id SERIAL PRIMARY KEY,
//     baby_id VARCHAR(255) NOT NULL,
//     user_id VARCHAR(255) NOT NULL,
//     type VARCHAR(50) NOT NULL, -- 'schedule_reminder', 'missed_vaccination', etc.
//     message TEXT NOT NULL,
//     notification_date DATE NOT NULL,
//     sms_sent BOOLEAN DEFAULT FALSE,
//     email_sent BOOLEAN DEFAULT FALSE,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// );

// -- Create indexes for better performance
// CREATE INDEX IF NOT EXISTS idx_notification_logs_baby_id ON notification_logs(baby_id);
// CREATE INDEX IF NOT EXISTS idx_notification_logs_user_id ON notification_logs(user_id);
// CREATE INDEX IF NOT EXISTS idx_notification_logs_date ON notification_logs(notification_date);
// CREATE INDEX IF NOT EXISTS idx_notification_logs_type ON notification_logs(type);
// CREATE TABLE IF NOT EXISTS system_settings (
//     id SERIAL PRIMARY KEY,
//     email_username VARCHAR(255) NOT NULL,
//     email_password VARCHAR(255) NOT NULL,
//     sms_api_key VARCHAR(255) NOT NULL,
//     sms_device_id VARCHAR(255) NOT NULL,
//     notification_time TIME DEFAULT '02:40:00',
//     system_name VARCHAR(255) DEFAULT 'eBakunado',
//     health_center_name VARCHAR(255) DEFAULT 'City Health Department, Ormoc City',
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// );

// -- Create index for better performance
// CREATE INDEX IF NOT EXISTS idx_system_settings_id ON system_settings(id);


// Function to initialize database tables (for Supabase setup)
function initializeSupabaseTables($supabase) {
    if (!$supabase) {
        return false;
    }
    
    // Note: Tables need to be created manually in Supabase dashboard
    // This function is for reference only
    
    // Insert default admin accounts
    $default_admins = [
        [
            'admin_id' => 'ADM001',
            'fname' => 'Default',
            'lname' => 'Admin',
            'email' => 'admin@gmail.com',
            'pass' => md5('admin123456')
        ],
        [
            'admin_id' => 'ADM002',
            'fname' => 'Default',
            'lname' => 'Admin',
            'email' => 'iquenxzx@gmail.com',
            'pass' => md5('iquen123456')
        ],
        [
            'admin_id' => 'ADM009',
            'fname' => 'Default',
            'lname' => 'Admin',
            'email' => 'james@gmail.com',
            'pass' => md5('james123456')
        ],
        [
            'admin_id' => 'ADM008',
            'fname' => 'Default',
            'lname' => 'Admin',
            'email' => 'jamesjus@gmail.com',
            'pass' => md5('james123456')
        ]
    ];
    
    // Insert default super admin
    $default_super_admin = [
        'super_admin_id' => 'SADM001',
        'fname' => 'Super',
        'lname' => 'Admin',
        'email' => 'superadmin@gmail.com',
        'pass' => md5('superadmin123456')
    ];
    
    // Check if admins exist and insert if not
    $existing_admins = $supabase->select('admin', 'admin_id');
    if ($existing_admins && count($existing_admins) === 0) {
        foreach ($default_admins as $admin) {
            $supabase->insert('admin', $admin);
        }
    }
    
    $existing_super_admin = $supabase->select('super_admin', 'super_admin_id');
    if ($existing_super_admin && count($existing_super_admin) === 0) {
        $supabase->insert('super_admin', $default_super_admin);
    }
    
    return true;
}

// Supabase-specific helper functions (no conflicts with built-in functions)
function supabase_query($sql) {
    global $supabase;
    if (!$supabase) return false;
    
    // This is a simplified version - complex queries will need to be rewritten
    error_log("supabase_query() called with SQL: $sql");
    error_log("Please use SupabaseDB methods instead of raw SQL");
    return false;
}

function supabase_prepare($sql) {
    global $supabase;
    if (!$supabase) return false;
    
    return $supabase->prepare($sql);
}

function supabase_error() {
    return "Use SupabaseDB methods for better error handling";
}

function supabase_num_rows($result) {
    if ($result instanceof SupabaseResult) {
        return $result->num_rows();
    }
    return 0;
}

function supabase_fetch_assoc($result) {
    if ($result instanceof SupabaseResult) {
        return $result->fetch_assoc();
    }
    return false;
}

// Make supabase instance globally available
$GLOBALS['supabase'] = $supabase;
?>
