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

    // Define lockout parameters
	$max_failed_attempts = 5; // Maximum allowed failed attempts
	$lockout_duration = 1800; // Lockout duration in seconds (30 minutes)

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
        error_log("check_staff_login: Attempting login for email: " . $e);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        // Check the result:
        if (mysqli_num_rows($result) == 1) {

            // Fetch the record:
            $row = mysqli_fetch_array ($result, MYSQLI_ASSOC);

            // Log current login attempt info
            error_log("check_staff_login: User found. Failed attempts: " . $row['failed_login_attempts'] . ", Status: " . $row['account_status'] . ", Lockout time: " . $row['lockout_time']);

            // Trim whitespace from the retrieved password hash
            $stored_hash = trim($row['password']);

            // **TEMPORARY LOGGING:** Log the provided password, stored hash, and verification result
            // error_log("Staff Login Attempt: Email=".$e.", Provided Password=".(isset($p)?$p:'EMPTY').", Stored Hash=".(isset($stored_hash)?$stored_hash:'NOT RETRIEVED').", password_verify Result=".(isset($p) && isset($stored_hash) && password_verify($p, $stored_hash) ? 'true' : 'false'));

            // Check if account is locked
            if ($row['account_status'] === 'locked') {
                $lockout_timestamp = strtotime($row['lockout_time']);
                if (time() - $lockout_timestamp < $lockout_duration) {
                    $time_remaining = $lockout_duration - (time() - $lockout_timestamp);
                    $minutes_remaining = ceil($time_remaining / 60);
                    $errors[] = 'Account locked due to too many failed login attempts. Please try again in about ' . $minutes_remaining . ' minute(s).';
                    // Log that the account is locked
                    error_log("check_staff_login: Account for user ID " . $row['id'] . " is locked.");
                    return array(false, $errors);
                } else {
                    // Lockout expired, reset failed attempts and status
                    $reset_q = "UPDATE staff SET failed_login_attempts = 0, account_status = 'active', lockout_time = NULL WHERE id = ?";
                    $reset_stmt = mysqli_prepare($dbc, $reset_q);
                    mysqli_stmt_bind_param($reset_stmt, 'i', $row['id']);
                    $reset_success = mysqli_stmt_execute($reset_stmt);
                    // Log reset
                    error_log("check_staff_login: Lockout expired for user ID " . $row['id'] . ". Resetting attempts and status. Success: " . ($reset_success ? 'true' : 'false'));
                }
            }

            // Apply progressive delay based on failed attempts (if not locked)
            if ($row['failed_login_attempts'] > 0) {
                $delay = min(pow(2, $row['failed_login_attempts']), 60);
                error_log("check_staff_login: Applying progressive delay of " . $delay . " seconds for user ID " . $row['id'] . ".");
                sleep($delay); // Cap delay at 60 seconds
            }

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
                 error_log("check_staff_login: Re-hashed password and reset login attempts for staff user ID: " . $row['id'] . ". Success: " . ($update_success ? 'true' : 'false'));

                 // Return true and the record (excluding sensitive data)
                unset($row['password']);
                unset($row['failed_login_attempts']);
                unset($row['account_status']);
                unset($row['lockout_time']);

                 // Log successful login (old hash updated)
                error_log("check_staff_login: Successful login and password re-hash for user ID: " . $row['id']);
                return array(true, $row);

            } else if (password_verify($p, $stored_hash)) {
                 // Password matches the modern hash. No re-hashing needed.

                 // Reset failed attempts and status on successful login
                 if ($row['failed_login_attempts'] > 0 || $row['account_status'] === 'locked') {
                    $reset_q = "UPDATE staff SET failed_login_attempts = 0, account_status = 'active', lockout_time = NULL WHERE id = ?";
                    $reset_stmt = mysqli_prepare($dbc, $reset_q);
                    mysqli_stmt_bind_param($reset_stmt, 'i', $row['id']);
                    $reset_success = mysqli_stmt_execute($reset_stmt);
                    // Optionally check for update success or log error
                     error_log("check_staff_login: Reset login attempts for staff user ID: " . $row['id'] . ". Success: " . ($reset_success ? 'true' : 'false'));
                 }

                 // Return true and the record (excluding sensitive data)
                unset($row['password']);
                unset($row['failed_login_attempts']);
                unset($row['account_status']);
                unset($row['lockout_time']);

                 // Log successful login (modern hash)
                error_log("check_staff_login: Successful login for user ID: " . $row['id']);
                return array(true, $row);

            } else {
                // Password does not match
                $errors[] = 'The email address and password entered do not match.';

                // Increment failed login attempts
                $new_attempts = ($row['failed_login_attempts'] ?? 0) + 1; // Use null coalescing operator for safety
                $update_q = "UPDATE staff SET failed_login_attempts = ?, last_failed_login = NOW() WHERE id = ?";
                $update_stmt = mysqli_prepare($dbc, $update_q);
                mysqli_stmt_bind_param($update_stmt, 'ii', $new_attempts, $row['id']);
                $update_success = mysqli_stmt_execute($update_stmt);
                // Optionally check for update success or log error
                error_log("check_staff_login: Password mismatch for staff user ID: " . $row['id'] . ": Incremented failed login attempts to: " . $new_attempts . ". Update success: " . ($update_success ? 'true' : 'false'));

                // Check if lockout threshold is reached and lock account
                if ($new_attempts >= $max_failed_attempts) {
                    $lock_q = "UPDATE staff SET account_status = 'locked', lockout_time = NOW() WHERE id = ?";
                    $lock_stmt = mysqli_prepare($dbc, $lock_q);
                    mysqli_stmt_bind_param($lock_stmt, 'i', $row['id']);
                    $lock_success = mysqli_stmt_execute($lock_stmt);
                    // Optionally check for update success or log error
                    error_log("check_staff_login: Lockout threshold reached. Account locked for staff user ID: " . $row['id'] . ". Lock success: " . ($lock_success ? 'true' : 'false'));
                    $errors[] = 'Account locked due to too many failed login attempts. Please try again later.'; // Add a general lockout message
                }

            }

        } else { 
            // Email not found or multiple users with the same email (shouldn't happen if email is unique)
            $errors[] = 'The email address and password entered do not match.';
            // Log email not found (without revealing if email exists)
            error_log("check_staff_login: Login failed - email not found or multiple users for email: " . $e);
            // Note: For security, we don't reveal if the email exists vs password is wrong.
            // We could implement IP-based rate limiting here if needed, but user-based is already handled above.
        }
        
    } // End of empty($errors) IF.
    
    // Log the errors array before returning
    error_log("check_staff_login: Returning with errors: " . print_r($errors, true));
    // Log the success status before returning
    error_log("check_staff_login: Returning success status: false");
    
    // Return false and the errors:
    return array(false, $errors);

} // End of check_staff_login() function. 
