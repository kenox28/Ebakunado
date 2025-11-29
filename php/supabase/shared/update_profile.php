<?php
session_start();
header('Content-Type: application/json');

// Handle both BHW and Midwife sessions
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? 'bhw';

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - User ID not found in session']);
    exit();
}

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

try {
    // Get form data
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $place = $_POST['place'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Get current user data BEFORE updating to check if user exists in users table
    // This is important because email/phone might be changing
    $currentUserData = null;
    if ($user_type === 'midwife') {
        $currentUserData = supabaseSelect('midwives', '*', ['midwife_id' => $user_id]);
    } else {
        $currentUserData = supabaseSelect('bhw', '*', ['bhw_id' => $user_id]);
    }
    
    $oldEmail = '';
    $oldPhone = '';
    $userExistsInUsersTable = false;
    $userUserId = null;
    
    // Debug: Log what we got from the query
    error_log("Current user data query result: " . json_encode($currentUserData));
    
    if ($currentUserData && !empty($currentUserData)) {
        // Handle different response formats
        $currentData = null;
        if (is_array($currentUserData)) {
            if (isset($currentUserData[0])) {
                $currentData = is_array($currentUserData[0]) ? $currentUserData[0] : (array)$currentUserData[0];
            } else {
                $currentData = $currentUserData;
            }
        } else {
            $currentData = (array)$currentUserData;
        }
        
        // Extract email and phone - try different possible column names
        $oldEmail = $currentData['email'] ?? $currentData['Email'] ?? '';
        $oldPhone = $currentData['phone_number'] ?? $currentData['phoneNumber'] ?? $currentData['phone'] ?? '';
        
        error_log("Extracted old email: $oldEmail, old phone: $oldPhone");
        
        // Check if user also exists in users table using OLD email/phone (before update)
        // Try to find user by old email first (check directly, not just if available_roles has 'user')
        if (!empty($oldEmail)) {
            $emailCheck = supabaseSelect('users', 'user_id', ['email' => $oldEmail]);
            if ($emailCheck && !empty($emailCheck)) {
                $emailResult = is_array($emailCheck[0]) ? $emailCheck[0] : (array)$emailCheck[0];
                $userUserId = $emailResult['user_id'] ?? $emailResult['userId'] ?? null;
                if ($userUserId) {
                    $userExistsInUsersTable = true;
                    error_log("Found user in users table by email: $oldEmail, user_id: $userUserId");
                }
            }
        }
        
        // If not found by email, try old phone number
        if (!$userExistsInUsersTable && !empty($oldPhone)) {
            $phoneCheck = supabaseSelect('users', 'user_id', ['phone_number' => $oldPhone]);
            if ($phoneCheck && !empty($phoneCheck)) {
                $phoneResult = is_array($phoneCheck[0]) ? $phoneCheck[0] : (array)$phoneCheck[0];
                $userUserId = $phoneResult['user_id'] ?? $phoneResult['userId'] ?? null;
                if ($userUserId) {
                    $userExistsInUsersTable = true;
                    error_log("Found user in users table by phone: $oldPhone, user_id: $userUserId");
                }
            }
        }
        
        if (!$userExistsInUsersTable) {
            error_log("User not found in users table. Email: $oldEmail, Phone: $oldPhone");
        }
    } else {
        error_log("ERROR: Could not retrieve current user data from " . $user_type . " table for ID: $user_id");
    }
    
    // Prepare update data - only include fields that are provided (not empty)
    // This prevents empty strings from overwriting existing values
    $updateData = [
        'updated' => date('c')
    ];
    
    // Only add fields to update if they are provided and not empty
    if (!empty($fname)) {
        $updateData['fname'] = $fname;
    }
    if (!empty($lname)) {
        $updateData['lname'] = $lname;
    }
    if (!empty($email)) {
        $updateData['email'] = $email;
    }
    if (!empty($phone_number)) {
        $updateData['phone_number'] = $phone_number;
    }
    if (!empty($gender)) {
        $updateData['gender'] = $gender;
    }
    if (!empty($place)) {
        $updateData['place'] = $place;
    }
    
    // Handle password change if provided
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            echo json_encode(['status' => 'error', 'message' => 'New passwords do not match']);
            exit();
        }
        
        // Get current user data to verify current password
        if ($user_type === 'midwife') {
            $userData = supabaseSelect('midwives', 'pass, salt', ['midwife_id' => $user_id]);
        } else {
            $userData = supabaseSelect('bhw', 'pass, salt', ['bhw_id' => $user_id]);
        }
        
        if ($userData && !empty($userData)) {
            // Unwrap first row if needed
            if (is_array($userData) && isset($userData[0])) { $userData = $userData[0]; }
            $storedHash = $userData['pass'] ?? '';
            $salt = $userData['salt'] ?? '';
            
            // Verify current password using bcrypt (same scheme as create account/login)
            if (!password_verify($current_password . $salt, $storedHash)) {
                echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
                exit();
            }
            
            // Hash new password using bcrypt with a fresh salt
            $newSalt = bin2hex(random_bytes(32));
            $newHash = password_hash($new_password . $newSalt, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $updateData['pass'] = $newHash;
            $updateData['salt'] = $newSalt;
        }
    }
    
    // Get Supabase instance to verify connection
    $supabase = getSupabase();
    if (!$supabase) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed'
        ]);
        exit();
    }

    // Update user data in BHW/Midwife table
    if ($user_type === 'midwife' || $user_type === 'midwifes' || strtolower($user_type) === 'midwives') {
        $result = supabaseUpdate('midwives', $updateData, ['midwife_id' => $user_id]);
    } else {
        $result = supabaseUpdate('bhw', $updateData, ['bhw_id' => $user_id]);
    }
    
    if ($result !== false) {
        // If user exists in users table, sync the update
        if ($userExistsInUsersTable && $userUserId) {
            // Prepare update data for users table - only sync fields that were actually updated
            // Copy all fields from $updateData except 'pass' (users table uses 'passw')
            $usersUpdateData = [
                'updated' => date('c')
            ];
            
            // Only sync fields that were in the update
            if (isset($updateData['fname'])) {
                $usersUpdateData['fname'] = $updateData['fname'];
            }
            if (isset($updateData['lname'])) {
                $usersUpdateData['lname'] = $updateData['lname'];
            }
            if (isset($updateData['email'])) {
                $usersUpdateData['email'] = $updateData['email'];
            }
            if (isset($updateData['phone_number'])) {
                $usersUpdateData['phone_number'] = $updateData['phone_number'];
            }
            if (isset($updateData['gender'])) {
                $usersUpdateData['gender'] = $updateData['gender'];
            }
            if (isset($updateData['place'])) {
                $usersUpdateData['place'] = $updateData['place'];
            }
            
            // If password was changed, also update it in users table
            if (isset($updateData['pass']) && isset($updateData['salt'])) {
                // Note: users table uses 'passw' instead of 'pass'
                $usersUpdateData['passw'] = $updateData['pass'];
                $usersUpdateData['salt'] = $updateData['salt'];
            }
            
            // Update users table
            error_log("Attempting to sync profile update to users table. user_id: $userUserId, data: " . json_encode($usersUpdateData));
            $usersUpdateResult = supabaseUpdate('users', $usersUpdateData, ['user_id' => $userUserId]);
            
            // If users table update fails, log but don't fail the whole operation
            if ($usersUpdateResult === false) {
                error_log("ERROR: Failed to sync profile update to users table for user_id: $userUserId");
            } else {
                error_log("SUCCESS: Profile synced to users table for user_id: $userUserId");
            }
        } else {
            error_log("Skipping users table sync - user not found. userExistsInUsersTable: " . ($userExistsInUsersTable ? 'true' : 'false') . ", userUserId: " . ($userUserId ?? 'null'));
        }
        
        // Update session data - only update fields that were actually changed
        if (isset($updateData['fname'])) {
            $_SESSION['fname'] = $updateData['fname'];
        }
        if (isset($updateData['lname'])) {
            $_SESSION['lname'] = $updateData['lname'];
        }
        if (isset($updateData['email'])) {
            $_SESSION['email'] = $updateData['email'];
        }
        if (isset($updateData['phone_number'])) {
            $_SESSION['phone_number'] = $updateData['phone_number'];
        }
        
        // Determine success message based on what was updated
        $updateMessage = 'Profile updated successfully';
        if (!empty($current_password) && !empty($new_password)) {
            $updateMessage = 'Password changed successfully';
        } elseif (!empty($fname) || !empty($lname)) {
            $updateMessage = 'Name updated successfully';
        } elseif (!empty($email)) {
            $updateMessage = 'Email updated successfully';
        } elseif (!empty($phone_number)) {
            $updateMessage = 'Phone number updated successfully';
        } elseif (!empty($gender)) {
            $updateMessage = 'Gender updated successfully';
        } elseif (!empty($place)) {
            $updateMessage = 'Place updated successfully';
        }
        
        // Prepare response - only include fields that were actually updated
        $responseData = [];
        if (isset($updateData['fname'])) {
            $responseData['fname'] = $updateData['fname'];
        }
        if (isset($updateData['lname'])) {
            $responseData['lname'] = $updateData['lname'];
        }
        if (isset($updateData['email'])) {
            $responseData['email'] = $updateData['email'];
        }
        if (isset($updateData['phone_number'])) {
            $responseData['phone_number'] = $updateData['phone_number'];
        }
        if (isset($updateData['gender'])) {
            $responseData['gender'] = $updateData['gender'];
        }
        if (isset($updateData['place'])) {
            $responseData['place'] = $updateData['place'];
        }
        
        $response = [
            'status' => 'success',
            'message' => $updateMessage,
            'data' => $responseData
        ];
        
        // Only include debug info in development (optional)
        if (isset($_GET['debug']) && $_GET['debug'] === '1') {
            $response['debug'] = [
                'user_exists_in_users_table' => $userExistsInUsersTable,
                'user_user_id' => $userUserId,
                'old_email' => $oldEmail,
                'old_phone' => $oldPhone,
                'new_email' => $email,
                'new_phone' => $phone_number,
                'current_user_data_found' => !empty($currentUserData),
                'sync_attempted' => $userExistsInUsersTable && $userUserId,
                'sync_success' => isset($usersUpdateResult) ? ($usersUpdateResult !== false) : false,
                'user_type' => $user_type,
                'user_id' => $user_id
            ];
        }
        
        echo json_encode($response);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update profile. Please try again or contact support if the problem persists.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Profile update error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while updating your profile. Please try again.'
    ]);
}
?>
