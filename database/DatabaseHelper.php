<?php
// Database Helper Functions for Supabase Migration
// This file provides easy-to-use functions that maintain compatibility with existing code

// Set Philippines timezone
date_default_timezone_set('Asia/Manila');
ini_set('date.timezone', 'Asia/Manila');

require_once 'SupabaseConfig.php';

// Get Supabase instance
function getSupabase() {
    global $supabase;
    return $supabase;
}

// Helper function for SELECT queries
function supabaseSelect($table, $columns = '*', $where = [], $orderBy = null, $limit = null) {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    return $supabase->select($table, $columns, $where, $orderBy, $limit);
}

// Helper function for INSERT queries
function supabaseInsert($table, $data) {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    return $supabase->insert($table, $data);
}

// Helper function for UPDATE queries
function supabaseUpdate($table, $data, $where) {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    return $supabase->update($table, $data, $where);
}

// Helper function for DELETE queries
function supabaseDelete($table, $where) {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    return $supabase->delete($table, $where);
}

// Authentication helper functions
function supabaseLogin($email_or_phone, $password) {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    $user_found = false;
    $user_data = null;
    $user_type = null;
    
    // Check if input is email or phone number
    $is_email = filter_var($email_or_phone, FILTER_VALIDATE_EMAIL);
    
    if ($is_email) {
        // Check super admin table
        $result = $supabase->select('super_admin', '*', ['email' => $email_or_phone]);
        if ($result && count($result) > 0) {
            $user_data = $result[0];
            $user_type = 'super_admin';
            $user_found = true;
        }
        
        // Check admin table
        if (!$user_found) {
            $result = $supabase->select('admin', '*', ['email' => $email_or_phone]);
            if ($result && count($result) > 0) {
                $user_data = $result[0];
                $user_type = 'admin';
                $user_found = true;
            }
        }
        
        // Check BHW table
        if (!$user_found) {
            $result = $supabase->select('bhw', '*', ['email' => $email_or_phone]);
            if ($result && count($result) > 0) {
                $user_data = $result[0];
                $user_type = 'bhw';
                $user_found = true;
            }
        }
        
        // Check Midwives table
        if (!$user_found) {
            $result = $supabase->select('midwives', '*', ['email' => $email_or_phone]);
            if ($result && count($result) > 0) {
                $user_data = $result[0];
                $user_type = 'midwife';
                $user_found = true;
            }
        }
        
        // Check Users table
        if (!$user_found) {
            $result = $supabase->select('users', '*', ['email' => $email_or_phone]);
            if ($result && count($result) > 0) {
                $user_data = $result[0];
                $user_type = 'user';
                $user_found = true;
            }
        }
    } else {
        // Phone number login - check each table
        $tables = [
            'bhw' => 'bhw',
            'midwives' => 'midwife', 
            'users' => 'user'
        ];
        
        foreach ($tables as $table => $type) {
            $result = $supabase->select($table, '*', ['phone_number' => $email_or_phone]);
            if ($result && count($result) > 0) {
                $user_data = $result[0];
                $user_type = $type;
                $user_found = true;
                break;
            }
        }
    }
    
    if (!$user_found) {
        return false;
    }
    
    // Verify password based on user type
    $password_valid = false;
    
    if ($user_type === 'super_admin' || $user_type === 'admin') {
        // Admin and super admin use MD5
        $password_valid = (md5($password) === $user_data['pass']);
        
        // Fallback: check if password is stored as plain text
        if (!$password_valid && $password === $user_data['pass']) {
            $password_valid = true;
        }
    } else {
        // BHW, Midwives, and Users use password_verify with salt
        $stored_salt = $user_data['salt'] ?? '';
        $stored_hash = $user_data['pass'] ?? $user_data['passw'] ?? '';
        
        if (!empty($stored_salt)) {
            $password_with_salt = $password . $stored_salt;
            $password_valid = password_verify($password_with_salt, $stored_hash);
        } else {
            // Fallback: check if password is stored as plain text or MD5
            $password_valid = ($password === $stored_hash) || (md5($password) === $stored_hash);
        }
    }
    
    if ($password_valid) {
        return [
            'user_data' => $user_data,
            'user_type' => $user_type
        ];
    }
    
    return false;
}

// Log activity helper
function supabaseLogActivity($user_id, $user_type, $action_type, $description, $ip_address = null) {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    // Get current Philippines time
    $current_time = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $philippines_time = $current_time->format('Y-m-d H:i:s');
    
    $log_data = [
        'user_id' => $user_id,
        'user_type' => $user_type,
        'action_type' => $action_type,
        'description' => $description,
        'ip_address' => $ip_address ?: $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'created_at' => $philippines_time
    ];
    
    return $supabase->insert('activity_logs', $log_data);
}

// Get user by ID helper
function supabaseGetUser($user_id, $user_type) {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    $table_map = [
        'super_admin' => 'super_admin',
        'admin' => 'admin',
        'bhw' => 'bhw',
        'midwife' => 'midwives',
        'user' => 'users'
    ];
    
    $table = $table_map[$user_type] ?? null;
    if (!$table) return false;
    
    $id_column = $user_type . '_id';
    $result = $supabase->select($table, '*', [$id_column => $user_id]);
    
    return $result && count($result) > 0 ? $result[0] : false;
}

// Update user helper
function supabaseUpdateUser($user_id, $user_type, $data) {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    $table_map = [
        'super_admin' => 'super_admin',
        'admin' => 'admin',
        'bhw' => 'bhw',
        'midwife' => 'midwives',
        'user' => 'users'
    ];
    
    $table = $table_map[$user_type] ?? null;
    if (!$table) return false;
    
    $id_column = $user_type . '_id';
    return $supabase->update($table, $data, [$id_column => $user_id]);
}

// Check if user exists helper
function supabaseUserExists($email, $phone = null) {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    $tables = ['users', 'midwives', 'bhw', 'admin', 'super_admin'];
    
    foreach ($tables as $table) {
        // Check email
        $result = $supabase->select($table, 'id', ['email' => $email]);
        if ($result && count($result) > 0) {
            return ['exists' => true, 'table' => $table, 'field' => 'email'];
        }
        
        // Check phone if provided
        if ($phone) {
            $result = $supabase->select($table, 'id', ['phone_number' => $phone]);
            if ($result && count($result) > 0) {
                return ['exists' => true, 'table' => $table, 'field' => 'phone_number'];
            }
        }
    }
    
    return ['exists' => false];
}

// Get all users helper (for admin panels)
function supabaseGetAllUsers($user_type = null, $limit = null) {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    if ($user_type) {
        $table_map = [
            'super_admin' => 'super_admin',
            'admin' => 'admin',
            'bhw' => 'bhw',
            'midwife' => 'midwives',
            'user' => 'users'
        ];
        
        $table = $table_map[$user_type] ?? null;
        if (!$table) return false;
        
        return $supabase->select($table, '*', [], 'created_at.desc', $limit);
    } else {
        // Get all user types
        $all_users = [];
        $tables = [
            'users' => 'user',
            'midwives' => 'midwife',
            'bhw' => 'bhw',
            'admin' => 'admin',
            'super_admin' => 'super_admin'
        ];
        
        foreach ($tables as $table => $type) {
            $users = $supabase->select($table, '*', [], 'created_at.desc', $limit);
            if ($users) {
                foreach ($users as $user) {
                    $user['user_type'] = $type;
                    $all_users[] = $user;
                }
            }
        }
        
        return $all_users;
    }
}

// Search users helper
function supabaseSearchUsers($search_term, $user_type = null) {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    // This is a simplified search - Supabase supports more advanced text search
    $results = [];
    
    if ($user_type) {
        $table_map = [
            'super_admin' => 'super_admin',
            'admin' => 'admin',
            'bhw' => 'bhw',
            'midwife' => 'midwives',
            'user' => 'users'
        ];
        
        $table = $table_map[$user_type] ?? null;
        if (!$table) return false;
        
        // Search by name or email
        $name_results = $supabase->select($table, '*', ['fname' => $search_term]);
        $email_results = $supabase->select($table, '*', ['email' => $search_term]);
        
        if ($name_results) $results = array_merge($results, $name_results);
        if ($email_results) $results = array_merge($results, $email_results);
        
        return array_unique($results, SORT_REGULAR);
    } else {
        // Search all tables
        $tables = ['users', 'midwives', 'bhw', 'admin', 'super_admin'];
        
        foreach ($tables as $table) {
            $name_results = $supabase->select($table, '*', ['fname' => $search_term]);
            $email_results = $supabase->select($table, '*', ['email' => $search_term]);
            
            if ($name_results) {
                foreach ($name_results as $user) {
                    $user['user_type'] = $table;
                    $results[] = $user;
                }
            }
            if ($email_results) {
                foreach ($email_results as $user) {
                    $user['user_type'] = $table;
                    $results[] = $user;
                }
            }
        }
        
        return array_unique($results, SORT_REGULAR);
    }
}

// Count records helper
function supabaseCount($table, $where = []) {
    $supabase = getSupabase();
    if (!$supabase) return 0;
    
    $result = $supabase->select($table, 'id', $where);
    return $result ? count($result) : 0;
}

// Get dashboard stats helper
function supabaseGetDashboardStats() {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    $stats = [
        'total_users' => supabaseCount('users'),
        'total_midwives' => supabaseCount('midwives'),
        'total_bhw' => supabaseCount('bhw'),
        'total_admins' => supabaseCount('admin'),
        'total_super_admins' => supabaseCount('super_admin'),
        'total_child_records' => supabaseCount('child_health_records'),
        'total_immunization_records' => supabaseCount('immunization_records'),
        'total_activity_logs' => supabaseCount('activity_logs')
    ];
    
    return $stats;
}

// Pagination helper
function supabasePaginate($table, $page = 1, $limit = 10, $where = [], $orderBy = 'id.desc') {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    $offset = ($page - 1) * $limit;
    
    // Get total count
    $total = supabaseCount($table, $where);
    
    // Get paginated results
    $results = $supabase->select($table, '*', $where, $orderBy, $limit);
    
    return [
        'data' => $results ?: [],
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit)
    ];
}

// Error handling helper
function handleSupabaseError($result, $operation = 'database operation') {
    if ($result === false) {
        error_log("Supabase $operation failed");
        return false;
    }
    return $result;
}

// Transaction helper (Supabase doesn't support transactions in the same way, but we can simulate)
function supabaseTransaction($operations) {
    $supabase = getSupabase();
    if (!$supabase) return false;
    
    $results = [];
    $success = true;
    
    foreach ($operations as $operation) {
        $result = false;
        
        switch ($operation['type']) {
            case 'insert':
                $result = $supabase->insert($operation['table'], $operation['data']);
                break;
            case 'update':
                $result = $supabase->update($operation['table'], $operation['data'], $operation['where']);
                break;
            case 'delete':
                $result = $supabase->delete($operation['table'], $operation['where']);
                break;
        }
        
        if ($result === false) {
            $success = false;
            break;
        }
        
        $results[] = $result;
    }
    
    return $success ? $results : false;
}
?>
