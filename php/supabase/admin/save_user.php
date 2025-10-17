<?php
session_start();
include "../../../database/SupabaseConfig.php";
include "../../../database/DatabaseHelper.php";

// Set Philippines timezone
date_default_timezone_set('Asia/Manila');

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
	echo json_encode(array('status' => 'error', 'message' => 'Unauthorized access'));
	exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(array('status' => 'error', 'message' => 'Invalid request method'));
	exit();
}

$user_id = $_POST['user_id'] ?? '';
$fname = $_POST['fname'] ?? '';
$lname = $_POST['lname'] ?? '';
$email = $_POST['email'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';
$role = $_POST['role'] ?? '';
$gender = $_POST['gender'] ?? '';
$place = $_POST['place'] ?? '';

if(empty($user_id) || empty($fname) || empty($lname) || empty($email) || empty($phone_number)) {
	echo json_encode(array('status' => 'error', 'message' => 'User ID, first name, last name, email, and phone number are required'));
	exit();
}

try {
    // Helper: insert with fallback when profileImg column is missing
    $insert_with_fallback = function($table, $data) {
        $res = supabaseInsert($table, $data);
        if ($res === false) {
            $err = getSupabase() ? getSupabase()->getLastError() : null;
            if (is_string($err) && stripos($err, 'profileImg') !== false) {
                unset($data['profileImg']);
                $res = supabaseInsert($table, $data);
            }
        }
        return $res;
    };
	// Determine current table for the user
	$current_user = null;
	$current_table = null;
	$current_role = 'user';

	$rows = supabaseSelect('users', '*', ['user_id' => $user_id]);
	if ($rows && count($rows) > 0) {
		$current_user = $rows[0];
		$current_table = 'users';
		$current_role = $current_user['role'] ?? 'user';
	} else {
		$rows = supabaseSelect('bhw', '*', ['bhw_id' => $user_id]);
		if ($rows && count($rows) > 0) {
			$current_user = $rows[0];
			$current_table = 'bhw';
			$current_role = $current_user['role'] ?? 'bhw';
		} else {
			$rows = supabaseSelect('midwives', '*', ['midwife_id' => $user_id]);
			if ($rows && count($rows) > 0) {
				$current_user = $rows[0];
				$current_table = 'midwives';
				$current_role = $current_user['role'] ?? 'midwife';
			} else {
				echo json_encode(array('status' => 'error', 'message' => 'User not found'));
				exit();
			}
		}
	}

    // Helper lambdas to insert and delete
    $insert_user_from_record = function($record, $id_key) use ($fname,$lname,$email,$phone_number,$gender,$place,$insert_with_fallback) {
        return $insert_with_fallback('users', [
        'user_id' => $record[$id_key],
        'fname' => $fname,
        'lname' => $lname,
        'email' => $email,
        'passw' => $record['passw'] ?? $record['pass'] ?? '',
        'phone_number' => $phone_number,
        'salt' => $record['salt'] ?? null,
        'profileImg' => $record['profileImg'] ?? 'noimage.jpg',
        'gender' => $gender ?: ($record['gender'] ?? null),
        'place' => $place ?: ($record['place'] ?? null),
        'role' => 'user',
        'created_at' => date('c'),
        'updated' => date('c')
    ]);
};

    $insert_bhw_from_record = function($record, $id_key) use ($fname,$lname,$email,$phone_number,$gender,$place,$insert_with_fallback) {
        return $insert_with_fallback('bhw', [
        'bhw_id' => $record[$id_key],
        'fname' => $fname,
        'lname' => $lname,
        'email' => $email,
        'pass' => $record['pass'] ?? $record['passw'] ?? '',
        'phone_number' => $phone_number,
        'salt' => $record['salt'] ?? null,
        'profileImg' => $record['profileImg'] ?? 'noimage.jpg',
        'gender' => $gender ?: ($record['gender'] ?? null),
        'place' => $place ?: ($record['place'] ?? null),
        'permissions' => 'view',
        'role' => 'bhw',
        'created_at' => date('c'),
        'updated' => date('c')
    ]);
};

    $insert_midwife_from_record = function($record, $id_key) use ($fname,$lname,$email,$phone_number,$gender,$place,$insert_with_fallback) {
        $midwife_data = [
        'midwife_id' => $record[$id_key],
        'fname' => $fname,
        'lname' => $lname,
        'email' => $email,
        'pass' => $record['pass'] ?? $record['passw'] ?? '',
        'phone_number' => $phone_number,
        'salt' => $record['salt'] ?? null,
        'profileImg' => $record['profileImg'] ?? 'noimage.jpg',
        'gender' => $gender ?: ($record['gender'] ?? null),
        'place' => $place ?: ($record['place'] ?? null),
        'permissions' => 'view',
        'role' => 'midwife',
        'created_at' => date('c'),
        'updated' => date('c')
        ];
        
        error_log("Attempting to insert midwife data: " . json_encode($midwife_data));
        $result = $insert_with_fallback('midwives', $midwife_data);
        error_log("Midwife insert result: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        return $result;
};

	// If role changes, handle moves between tables
	if ($role !== $current_role) {
		if ($role === 'user') {
			if ($current_table === 'bhw') {
				$ins = $insert_user_from_record($current_user, 'bhw_id');
				if ($ins === false) { echo json_encode(['status'=>'error','message'=>'Failed to move user from BHW to users table']); exit(); }
				$del = supabaseDelete('bhw', ['bhw_id' => $user_id]);
				if ($del === false) { echo json_encode(['status'=>'error','message'=>'Failed to remove user from BHW table']); exit(); }
			} elseif ($current_table === 'midwives') {
				$ins = $insert_user_from_record($current_user, 'midwife_id');
				if ($ins === false) { echo json_encode(['status'=>'error','message'=>'Failed to move user from Midwives to users table']); exit(); }
				$del = supabaseDelete('midwives', ['midwife_id' => $user_id]);
				if ($del === false) { echo json_encode(['status'=>'error','message'=>'Failed to remove user from Midwives table']); exit(); }
			} else {
				$upd = supabaseUpdate('users', [
					'fname' => $fname,
					'lname' => $lname,
					'email' => $email,
					'phone_number' => $phone_number,
					'gender' => $gender,
					'place' => $place,
					'role' => $role
				], ['user_id' => $user_id]);
				if ($upd === false) { echo json_encode(['status'=>'error','message'=>'Failed to update user']); exit(); }
			}
		} elseif ($role === 'bhw') {
			if ($current_table === 'users') {
				$ins = $insert_bhw_from_record($current_user, 'user_id');
                if ($ins === false) { 
                    $dbg = getSupabase() ? ['status' => getSupabase()->getLastStatus(), 'error' => getSupabase()->getLastError()] : null;
                    echo json_encode(['status'=>'error','message'=>'Failed to move user to BHW table','debug'=>$dbg]); 
                    exit(); 
                }
				$del = supabaseDelete('users', ['user_id' => $user_id]);
				if ($del === false) { echo json_encode(['status'=>'error','message'=>'Failed to remove user from users table']); exit(); }
			} elseif ($current_table === 'midwives') {
				$ins = $insert_bhw_from_record($current_user, 'midwife_id');
				if ($ins === false) { echo json_encode(['status'=>'error','message'=>'Failed to move user to BHW table']); exit(); }
				$del = supabaseDelete('midwives', ['midwife_id' => $user_id]);
				if ($del === false) { echo json_encode(['status'=>'error','message'=>'Failed to remove user from midwives table']); exit(); }
			} else {
				$upd = supabaseUpdate('bhw', [
					'fname' => $fname,
					'lname' => $lname,
					'email' => $email,
					'phone_number' => $phone_number,
					'gender' => $gender,
					'place' => $place
				], ['bhw_id' => $user_id]);
				if ($upd === false) { echo json_encode(['status'=>'error','message'=>'Failed to update BHW']); exit(); }
			}
		} elseif ($role === 'midwife') {
			if ($current_table === 'users') {
				error_log("Attempting to move user from users to midwives table. User data: " . json_encode($current_user));
				$ins = $insert_midwife_from_record($current_user, 'user_id');
				if ($ins === false) { 
					$dbg = getSupabase() ? ['status' => getSupabase()->getLastStatus(), 'error' => getSupabase()->getLastError()] : null;
					error_log("Failed to insert into midwives table. Debug: " . json_encode($dbg));
					echo json_encode(['status'=>'error','message'=>'Failed to move user to midwives table','debug'=>$dbg]); 
					exit(); 
				}
				error_log("Successfully inserted into midwives table, now deleting from users table");
				$del = supabaseDelete('users', ['user_id' => $user_id]);
				if ($del === false) { 
					error_log("Failed to delete from users table");
					echo json_encode(['status'=>'error','message'=>'Failed to remove user from users table']); 
					exit(); 
				}
			} elseif ($current_table === 'bhw') {
				error_log("Attempting to move user from bhw to midwives table. User data: " . json_encode($current_user));
				$ins = $insert_midwife_from_record($current_user, 'bhw_id');
				if ($ins === false) { 
					$dbg = getSupabase() ? ['status' => getSupabase()->getLastStatus(), 'error' => getSupabase()->getLastError()] : null;
					error_log("Failed to insert into midwives table. Debug: " . json_encode($dbg));
					echo json_encode(['status'=>'error','message'=>'Failed to move user to midwives table','debug'=>$dbg]); 
					exit(); 
				}
				error_log("Successfully inserted into midwives table, now deleting from bhw table");
				$del = supabaseDelete('bhw', ['bhw_id' => $user_id]);
				if ($del === false) { 
					error_log("Failed to delete from bhw table");
					echo json_encode(['status'=>'error','message'=>'Failed to remove user from BHW table']); 
					exit(); 
				}
			} else {
				$upd = supabaseUpdate('midwives', [
					'fname' => $fname,
					'lname' => $lname,
					'email' => $email,
					'phone_number' => $phone_number,
					'gender' => $gender,
					'place' => $place
				], ['midwife_id' => $user_id]);
				if ($upd === false) { echo json_encode(['status'=>'error','message'=>'Failed to update midwife']); exit(); }
			}
		}
	} else {
		// No role change, update in current table
		if ($current_table === 'users') {
			$upd = supabaseUpdate('users', [
				'fname' => $fname,
				'lname' => $lname,
				'email' => $email,
				'phone_number' => $phone_number,
				'gender' => $gender,
				'place' => $place
			], ['user_id' => $user_id]);
		} elseif ($current_table === 'bhw') {
			$upd = supabaseUpdate('bhw', [
				'fname' => $fname,
				'lname' => $lname,
				'email' => $email,
				'phone_number' => $phone_number,
				'gender' => $gender,
				'place' => $place
			], ['bhw_id' => $user_id]);
		} else {
			$upd = supabaseUpdate('midwives', [
				'fname' => $fname,
				'lname' => $lname,
				'email' => $email,
				'phone_number' => $phone_number,
				'gender' => $gender,
				'place' => $place
			], ['midwife_id' => $user_id]);
		}
		if ($upd === false) { echo json_encode(['status'=>'error','message'=>'Failed to update user']); exit(); }
	}

	// Log activity
	$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	$admin_type = isset($_SESSION['super_admin_id']) ? 'super_admin' : 'admin';
	$admin_id = isset($_SESSION['super_admin_id']) ? $_SESSION['super_admin_id'] : $_SESSION['admin_id'];
	$description = "User role changed by admin: " . $admin_id . " from " . $current_role . " to " . $role;
	supabaseLogActivity($user_id, $admin_type, 'user_update', $description, $ip);

	echo json_encode(array('status' => 'success', 'message' => 'User updated successfully'));

} catch (Exception $e) {
	error_log("Save user error: " . $e->getMessage());
	echo json_encode(array('status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()));
}
?>


