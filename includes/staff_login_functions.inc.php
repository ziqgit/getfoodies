<?php 
// This page defines a function used by the staff login/logout process.

/* This function validates the form data (the email address and password).
 * If both are present, the database is queried.
 * The function requires a database connection.
 * The function returns an array of information, including:
 * - a TRUE/FALSE variable indicating success
 * - an array of either errors or the database result
 */
function check_staff_login($dbc, $email = '', $pass1 = '') {
    $errors = array(); // Initialize error array.

    // Define user-based lockout parameters
	$max_failed_attempts_user = 5; // Maximum allowed failed attempts per user
	$lockout_duration_user = 1800; // User lockout duration in seconds (30 minutes)

    // Define IP-based lockout parameters
    $max_failed_attempts_ip = 10; // Maximum allowed failed attempts per IP
    $lockout_duration_ip = 300; // IP lockout duration in seconds (5 minutes)

    // Get client IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Check IP-based lockout first
    $ip_check_q = "SELECT failed_attempts, lockout_until FROM ip_failed_logins WHERE ip_address = ?";
    $ip_check_stmt = mysqli_prepare($dbc, $ip_check_q);
    mysqli_stmt_bind_param($ip_check_stmt, 's', $ip_address);
    mysqli_stmt_execute($ip_check_stmt);
    $ip_check_result = mysqli_stmt_get_result($ip_check_stmt);

    if (mysqli_num_rows($ip_check_result) > 0) {
        $ip_row = mysqli_fetch_assoc($ip_check_result);
        $ip_lockout_until = strtotime($ip_row['lockout_until']);

        if ($ip_lockout_until > time()) {
            // IP is currently locked
            $time_remaining = $ip_lockout_until - time();
            $minutes_remaining = ceil($time_remaining / 60);
            $errors[] = 'Too many failed login attempts from your IP address. Please try again in about ' . $minutes_remaining . ' minute(s).';
            error_log("check_staff_login: IP address " . $ip_address . " is locked. Lockout until: " . $ip_row['lockout_until']);
            return array(false, $errors);
        } else if ($ip_row['lockout_until'] !== NULL) {
            // IP lockout has expired, reset failed attempts for this IP
            $reset_ip_q = "UPDATE ip_failed_logins SET failed_attempts = 0, lockout_until = NULL WHERE ip_address = ?";
            $reset_ip_stmt = mysqli_prepare($dbc, $reset_ip_q);
            mysqli_stmt_bind_param($reset_ip_stmt, 's', $ip_address);
            mysqli_stmt_execute($reset_ip_stmt);
            error_log("check_staff_login: IP lockout expired for " . $ip_address . ". Resetting attempts.");
        }
        // If lockout_until is NULL, it means the IP wasn't locked, so do nothing here.
    }

    // Validate the email address:
    if (empty($email)) {
        $errors[] = 'You forgot to enter your email address.';
    } else {
        // Use prepared statements to prevent SQL injection
        $e = trim($email);
    }

    // Validate the password:
    if (empty($pass1)) {
        $errors[] = 'You forgot to enter your password.';
    } else {
        // No need to escape password yet, will be verified against hash
        $p = trim($pass1);
    }

    if (empty($errors)) { // If everything's OK.
        // Retrieve the id, name, and password for that email:
        // Use prepared statements to prevent SQL injection
        $q = "SELECT id, name, password, failed_login_attempts, account_status, lockout_time FROM staff WHERE email = ?";
        $stmt = mysqli_prepare($dbc, $q);
        mysqli_stmt_bind_param($stmt, 's', $e);
        // Log the email being checked
        error_log("check_staff_login: Attempting user login for email: " . $e);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        // Check the result:
        if (mysqli_num_rows($result) == 1) {

            // Fetch the record:
            $row = mysqli_fetch_array ($result, MYSQLI_ASSOC);

            // Log current user login attempt info
            error_log("check_staff_login: User found. Failed attempts: " . $row['failed_login_attempts'] . ", Status: " . $row['account_status'] . ", Lockout time: " . $row['lockout_time']);

            // Check if user account is locked
            if ($row['account_status'] === 'locked') {
                $lockout_timestamp = strtotime($row['lockout_time']);
                if (time() - $lockout_timestamp < $lockout_duration_user) {
                    $time_remaining = $lockout_duration_user - (time() - $lockout_timestamp);
                    $minutes_remaining = ceil($time_remaining / 60);
                    $errors[] = 'Account locked due to too many failed login attempts. Please try again in about ' . $minutes_remaining . ' minute(s).';
                    // Log that the user account is locked
                    error_log("check_staff_login: User account for user ID " . $row['id'] . " is locked.");

                    // Also increment IP failed attempts for a locked user account attempt
                    increment_ip_failed_attempts($dbc, $ip_address, $max_failed_attempts_ip, $lockout_duration_ip);

                    return array(false, $errors);
                } else {
                    // User lockout expired, reset failed attempts and status
                    $reset_q = "UPDATE staff SET failed_login_attempts = 0, account_status = 'active', lockout_time = NULL WHERE id = ?";
                    $reset_stmt = mysqli_prepare($dbc, $reset_q);
                    mysqli_stmt_bind_param($reset_stmt, 'i', $row['id']);
                    $reset_success = mysqli_stmt_execute($reset_stmt);
                    // Log user reset
                    error_log("check_staff_login: User lockout expired for user ID " . $row['id'] . ". Resetting attempts and status. Success: " . ($reset_success ? 'true' : 'false'));
                }
            }

            // Apply progressive delay based on user failed attempts (if not locked)
            if ($row['failed_login_attempts'] > 0) {
                $delay = min(pow(2, $row['failed_login_attempts']), 60);
                error_log("check_staff_login: Applying progressive user delay of " . $delay . " seconds for user ID " . $row['id'] . ".");
                sleep($delay); // Cap delay at 60 seconds
            }

            // Trim whitespace from the retrieved password hash
            $stored_hash = trim($row['password']);

            // **TEMPORARY LOGGING:** Log the provided password, stored hash, and verification result
            // error_log("Staff Login Attempt: Email=".$e.", Provided Password=".(isset($p)?$p:'EMPTY').", Stored Hash=".(isset($stored_hash)?$stored_hash:'NOT RETRIEVED').", password_verify Result=".(isset($p) && isset($stored_hash) && password_verify($p, $stored_hash) ? 'true' : 'false'));

            // Verify the password against the stored hash
            // SHA1 hashes are 40 characters long in hex representation
            if (strlen($stored_hash) === 40 && password_verify($p, $stored_hash)) {
                // Password matches, but it's an old SHA1 hash. Re-hash and update.
                $new_hash = password_hash($p, PASSWORD_DEFAULT);
                $update_q = "UPDATE staff SET password = ?, failed_login_attempts = 0, account_status = 'active', lockout_time = NULL WHERE id = ?";
                $update_stmt = mysqli_prepare($dbc, $update_q);
                mysqli_stmt_bind_param($update_stmt, 'si', $new_hash, $row['id']);
                $update_success = mysqli_stmt_execute($update_stmt);
                // Optionally check for update success or log an error
                 error_log("check_staff_login: Re-hashed password and reset user login attempts for staff user ID: " . $row['id'] . ". Success: " . ($update_success ? 'true' : 'false'));

                 // Reset IP failed attempts on successful user login
                reset_ip_failed_attempts($dbc, $ip_address);

                 // Return true and the record (excluding sensitive data)
                unset($row['password']);
                unset($row['failed_login_attempts']);
                unset($row['account_status']);
                unset($row['lockout_time']);

                 // Log successful login (old hash updated)
                error_log("check_staff_login: Successful user login and password re-hash for user ID: " . $row['id']);
                return array(true, $row);

            } else if (password_verify($p, $stored_hash)) {
                 // Password matches the modern hash. No re-hashing needed.

                 // Reset failed attempts and status on successful user login
                 if ($row['failed_login_attempts'] > 0 || $row['account_status'] === 'locked') {
                    $reset_q = "UPDATE staff SET failed_login_attempts = 0, account_status = 'active', lockout_time = NULL WHERE id = ?";
                    $reset_stmt = mysqli_prepare($dbc, $reset_q);
                    mysqli_stmt_bind_param($reset_stmt, 'i', $row['id']);
                    $reset_success = mysqli_stmt_execute($reset_stmt);
                    // Optionally check for update success or log error
                     error_log("check_staff_login: Reset user login attempts for staff user ID: " . $row['id'] . ". Success: " . ($reset_success ? 'true' : 'false'));
                 }

                 // Reset IP failed attempts on successful user login
                reset_ip_failed_attempts($dbc, $ip_address);

                 // Return true and the record (excluding sensitive data)
                unset($row['password']);
                unset($row['failed_login_attempts']);
                unset($row['account_status']);
                unset($row['lockout_time']);

                 // Log successful login (modern hash)
                error_log("check_staff_login: Successful user login for user ID: " . $row['id']);
                return array(true, $row);

            } else {
                // Password does not match for existing user
                $errors[] = 'The email address and password entered do not match.';

                // Increment user failed login attempts
                $new_attempts_user = ($row['failed_login_attempts'] ?? 0) + 1; // Use null coalescing operator for safety
                $update_q = "UPDATE staff SET failed_login_attempts = ?, last_failed_login = NOW() WHERE id = ?";
                $update_stmt = mysqli_prepare($dbc, $update_q);
                mysqli_stmt_bind_param($update_stmt, 'ii', $new_attempts_user, $row['id']);
                $update_success_user = mysqli_stmt_execute($update_stmt);
                // Optionally check for update success or log error
                error_log("check_staff_login: Password mismatch for staff user ID: " . $row['id'] . ": Incremented user failed login attempts to: " . $new_attempts_user . ". Update success: " . ($update_success_user ? 'true' : 'false'));

                // Add attempts remaining warning if not yet locked
                if ($new_attempts_user < $max_failed_attempts_user) {
                    $attempts_remaining = $max_failed_attempts_user - $new_attempts_user;
                    $errors[] = 'You have ' . $attempts_remaining . ' login attempt(s) remaining before your account is locked.';
                     error_log("check_staff_login: User ID " . $row['id'] . " has " . $attempts_remaining . " attempts remaining.");
                }

                // Check if user lockout threshold is reached and lock account
                if ($new_attempts_user >= $max_failed_attempts_user) {
                    $lock_q = "UPDATE staff SET account_status = 'locked', lockout_time = NOW() WHERE id = ?";
                    $lock_stmt = mysqli_prepare($dbc, $lock_q);
                    mysqli_stmt_bind_param($lock_stmt, 'i', $row['id']);
                    $lock_success_user = mysqli_stmt_execute($lock_stmt);
                    // Optionally check for update success or log error
                    error_log("check_staff_login: User lockout threshold reached. Account locked for staff user ID: " . $row['id'] . ". Lock success: " . ($lock_success_user ? 'true' : 'false'));
                    $errors[] = 'Account locked due to too many failed login attempts. Please try again later.'; // Add a general lockout message
                }

                // Also increment IP failed attempts for a failed user login
                increment_ip_failed_attempts($dbc, $ip_address, $max_failed_attempts_ip, $lockout_duration_ip);

            }

        } else {
            // Email not found or multiple users with the same email (shouldn't happen if email is unique)
            $errors[] = 'The email address and password entered do not match.';
            // Log email not found (without revealing if email exists)
            error_log("check_staff_login: Login failed - email not found or multiple users for email: " . $e);

            // Increment IP failed attempts for email not found
            increment_ip_failed_attempts($dbc, $ip_address, $max_failed_attempts_ip, $lockout_duration_ip);

            // Note: For security, we don't reveal if the email exists vs password is wrong.
            // We could implement IP-based rate limiting here if needed, but user-based is already handled above.
        }
        
    } else {
        // If there are errors before checking credentials (e.g., empty email/password)
        // Increment IP failed attempts for these errors as well
        error_log("check_staff_login: Errors found before credential check: " . print_r($errors, true));
        increment_ip_failed_attempts($dbc, $ip_address, $max_failed_attempts_ip, $lockout_duration_ip);
    }
    
    // Log the errors array before returning
    error_log("check_staff_login: Returning with errors: " . print_r($errors, true));
    // Log the success status before returning
    error_log("check_staff_login: Returning success status: false");
    
    // Return false and the errors:
    return array(false, $errors);

} // End of check_staff_login() function. 

// Helper function to increment IP failed attempts and apply IP lockout
function increment_ip_failed_attempts($dbc, $ip_address, $max_attempts, $lockout_duration) {
    error_log("increment_ip_failed_attempts: Processing IP: " . $ip_address);
    // Check if IP exists in the table
    $check_q = "SELECT failed_attempts FROM ip_failed_logins WHERE ip_address = ?";
    $check_stmt = mysqli_prepare($dbc, $check_q);
    if (!$check_stmt) {
        error_log("increment_ip_failed_attempts: Failed to prepare select statement: " . mysqli_error($dbc));
        return;
    }
    mysqli_stmt_bind_param($check_stmt, 's', $ip_address);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($result) > 0) {
        // IP exists, update failed attempts
        $row = mysqli_fetch_assoc($result);
        $current_attempts = $row['failed_attempts'];
        $new_attempts = $current_attempts + 1;
        error_log("increment_ip_failed_attempts: IP found. Current attempts: " . $current_attempts . ". New attempts: " . $new_attempts);

        $update_q = "UPDATE ip_failed_logins SET failed_attempts = ?, last_attempt_time = NOW() WHERE ip_address = ?";
        $update_stmt = mysqli_prepare($dbc, $update_q);
        if (!$update_stmt) {
            error_log("increment_ip_failed_attempts: Failed to prepare update statement: " . mysqli_error($dbc));
            return;
        }
        mysqli_stmt_bind_param($update_stmt, 'is', $new_attempts, $ip_address);
        $update_success = mysqli_stmt_execute($update_stmt);

        error_log("increment_ip_failed_attempts: Updated failed attempts for IP " . $ip_address . " to " . $new_attempts . ". Update success: " . ($update_success ? 'true' : 'false'));

        // Check if IP lockout threshold is reached
        if ($new_attempts >= $max_attempts) {
            $lockout_until = date('Y-m-d H:i:s', time() + $lockout_duration);
            $lock_q = "UPDATE ip_failed_logins SET lockout_until = ? WHERE ip_address = ?";
            $lock_stmt = mysqli_prepare($dbc, $lock_q);
             if (!$lock_stmt) {
                error_log("increment_ip_failed_attempts: Failed to prepare lockout statement: " . mysqli_error($dbc));
                return;
            }
            mysqli_stmt_bind_param($lock_stmt, 'ss', $lockout_until, $ip_address);
            $lock_success = mysqli_stmt_execute($lock_stmt);
            error_log("increment_ip_failed_attempts: IP lockout threshold reached. Locking IP " . $ip_address . " until " . $lockout_until . ". Lock success: " . ($lock_success ? 'true' : 'false'));
        }

    } else {
        // IP does not exist, insert new record
        error_log("increment_ip_failed_attempts: IP not found. Inserting new record for IP " . $ip_address);
        $insert_q = "INSERT INTO ip_failed_logins (ip_address, failed_attempts, last_attempt_time) VALUES (?, 1, NOW())";
        $insert_stmt = mysqli_prepare($dbc, $insert_q);
         if (!$insert_stmt) {
            error_log("increment_ip_failed_attempts: Failed to prepare insert statement: " . mysqli_error($dbc));
            return;
        }
        mysqli_stmt_bind_param($insert_stmt, 's', $ip_address);
        $insert_success = mysqli_stmt_execute($insert_stmt);
        error_log("increment_ip_failed_attempts: New record insertion for IP " . $ip_address . ". Failed attempts: 1. Insert success: " . ($insert_success ? 'true' : 'false'));
    }
}

// Helper function to reset IP failed attempts on successful login
function reset_ip_failed_attempts($dbc, $ip_address) {
     error_log("reset_ip_failed_attempts: Attempting to reset failed attempts for IP " . $ip_address);
    $reset_q = "UPDATE ip_failed_logins SET failed_attempts = 0, lockout_until = NULL WHERE ip_address = ?";
    $reset_stmt = mysqli_prepare($dbc, $reset_q);
     if (!$reset_stmt) {
        error_log("reset_ip_failed_attempts: Failed to prepare reset statement: " . mysqli_error($dbc));
        return;
    }
    mysqli_stmt_bind_param($reset_stmt, 's', $ip_address);
    $reset_success = mysqli_stmt_execute($reset_stmt);
    error_log("reset_ip_failed_attempts: Resetting failed attempts for IP " . $ip_address . ". Success: " . ($reset_success ? 'true' : 'false'));
} 
