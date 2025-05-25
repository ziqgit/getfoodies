<?php 
// This page defines two functions used by the login/logout process.

/* This function determines an absolute URL and redirects the user there.
 * The function takes one argument: the page to be redirected to.
 * The argument defaults to index.php.
 */
function redirect_user ($page = 'index.php') {

	// Start defining the URL...
	// URL is http:// plus the host name plus the current directory:
	$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	
	// Remove any trailing slashes:
	$url = rtrim($url, '/\\'); 
	
	// Add the page:
	$url .= '/' . $page;
	
	// Redirect the user:
	header("Location: $url");
	exit(); // Quit the script.

} // End of redirect_user() function.


/* This function validates the form data (the email address and password).
 * If both are present, the database is queried.
 * The function requires a database connection.
 * The function returns an array of information, including:
 * - a TRUE/FALSE variable indicating success
 * - an array of either errors or the database result
 */
function check_login($dbc, $email = '', $pass1 = '') {

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

		// Retrieve user data including login attempt info:
        // Use prepared statements to prevent SQL injection
		$q = "SELECT user_id, name, password, failed_login_attempts, account_status, lockout_time FROM admin WHERE email = ?";
		$stmt = mysqli_prepare($dbc, $q);
        mysqli_stmt_bind_param($stmt, 's', $e);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
		
		// Check the result:
		if (mysqli_num_rows($result) == 1) {

			// Fetch the record:
			$row = mysqli_fetch_array ($result, MYSQLI_ASSOC);

            // Trim whitespace from the retrieved password hash
            $stored_hash = trim($row['password']);


            // Check if account is locked
            if ($row['account_status'] === 'locked') {
                $lockout_timestamp = strtotime($row['lockout_time']);
                if (time() - $lockout_timestamp < $lockout_duration) {
                    $time_remaining = $lockout_duration - (time() - $lockout_timestamp);
                    $minutes_remaining = ceil($time_remaining / 60);
                    $errors[] = 'Account locked due to too many failed login attempts. Please try again in about ' . $minutes_remaining . ' minute(s).';
                    return array(false, $errors);
                } else {
                    // Lockout expired, reset failed attempts and status
                    $reset_q = "UPDATE admin SET failed_login_attempts = 0, account_status = 'active', lockout_time = NULL WHERE user_id = ?";
                    $reset_stmt = mysqli_prepare($dbc, $reset_q);
                    mysqli_stmt_bind_param($reset_stmt, 'i', $row['user_id']);
                    mysqli_stmt_execute($reset_stmt);
                }
            }

            // Apply progressive delay based on failed attempts (if not locked)
            if ($row['failed_login_attempts'] > 0) {
                sleep(min(pow(2, $row['failed_login_attempts']), 60)); // Cap delay at 60 seconds
            }


			// Verify the password against the stored hash
            // SHA1 hashes are 40 characters long in hex representation
            if (strlen($stored_hash) === 40 && password_verify($p, $stored_hash)) {
                // Password matches, but it's an old SHA1 hash. Re-hash and update.
                $new_hash = password_hash($p, PASSWORD_DEFAULT);
                $update_q = "UPDATE admin SET password = ?, failed_login_attempts = 0, account_status = 'active', lockout_time = NULL WHERE user_id = ?";
                $update_stmt = mysqli_prepare($dbc, $update_q);
                mysqli_stmt_bind_param($update_stmt, 'si', $new_hash, $row['user_id']);
                mysqli_stmt_execute($update_stmt);
                // Optionally check for update success or log an error
                error_log("Re-hashed password and reset login attempts for admin user ID: " . $row['user_id']);

                 // Return true and the record (excluding sensitive data)
                unset($row['password']);
                unset($row['failed_login_attempts']);
                unset($row['account_status']);
                unset($row['lockout_time']);

                return array(true, $row);

            } else if (password_verify($p, $stored_hash)) {
                 // Password matches the modern hash. No re-hashing needed.

                 // Reset failed attempts and status on successful login
                 if ($row['failed_login_attempts'] > 0 || $row['account_status'] === 'locked') {
                    $reset_q = "UPDATE admin SET failed_login_attempts = 0, account_status = 'active', lockout_time = NULL WHERE user_id = ?";
                    $reset_stmt = mysqli_prepare($dbc, $reset_q);
                    mysqli_stmt_bind_param($reset_stmt, 'i', $row['user_id']);
                    mysqli_stmt_execute($reset_stmt);
                    // Optionally check for update success or log error
                     error_log("Reset login attempts for admin user ID: " . $row['user_id']);
                 }

                 // Return true and the record (excluding sensitive data)
                unset($row['password']);
                unset($row['failed_login_attempts']);
                unset($row['account_status']);
                unset($row['lockout_time']);

                return array(true, $row);

            } else {
                // Password does not match
                $errors[] = 'The email address and password entered do not match.';

                // Increment failed login attempts
                $new_attempts = ($row['failed_login_attempts'] ?? 0) + 1; // Use null coalescing operator for safety
                $update_q = "UPDATE admin SET failed_login_attempts = ? WHERE user_id = ?";
                $update_stmt = mysqli_prepare($dbc, $update_q);
                mysqli_stmt_bind_param($update_stmt, 'ii', $new_attempts, $row['user_id']);
                mysqli_stmt_execute($update_stmt);
                // Optionally check for update success or log error
                error_log("Incremented failed login attempts for admin user ID: " . $row['user_id'] . ": " . $new_attempts);

                // Check if lockout threshold is reached and lock account
                if ($new_attempts >= $max_failed_attempts) {
                    $lock_q = "UPDATE admin SET account_status = 'locked', lockout_time = NOW() WHERE user_id = ?";
                    $lock_stmt = mysqli_prepare($dbc, $lock_q);
                    mysqli_stmt_bind_param($lock_stmt, 'i', $row['user_id']);
                    mysqli_stmt_execute($lock_stmt);
                    // Optionally check for update success or log error
                    error_log("Account locked for admin user ID: " . $row['user_id']);
                    $errors[] = 'Account locked due to too many failed login attempts. Please try again later.'; // Add a general lockout message
                }

            }

        } else { 
            // Email not found or multiple users with the same email (shouldn't happen if email is unique)
			$errors[] = 'The email address and password entered do not match.';
            // Note: For security, we don't reveal if the email exists vs password is wrong.
            // We could implement IP-based rate limiting here if needed, but user-based is already handled above.
        }
		
	} // End of empty($errors) IF.
	
	// Return false and the errors:
	return array(false, $errors);

} // End of check_login() function.
