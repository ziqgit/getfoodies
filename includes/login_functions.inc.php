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

		// Retrieve the user_id, name, and password for that email:
        // Use prepared statements to prevent SQL injection
		$q = "SELECT user_id, name, password FROM admin WHERE email = ?";
		$stmt = mysqli_prepare($dbc, $q);
        mysqli_stmt_bind_param($stmt, 's', $e);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
		
		// Check the result:
		if (mysqli_num_rows($result) == 1) {

			// Fetch the record:
			$row = mysqli_fetch_array ($result, MYSQLI_ASSOC);

            // **TEMPORARY LOGGING:** Log the provided password, stored hash, and verification result
            error_log("Login Attempt: Email=".$e.", Provided Password=".(isset($p)?$p:'EMPTY').", Stored Hash=".(isset($row['password'])?$row['password']:'NOT RETRIEVED').", password_verify Result=".(isset($p) && isset($row['password']) && password_verify($p, $row['password']) ? 'true' : 'false'));

			// Verify the password against the stored hash
            // SHA1 hashes are 40 characters long in hex representation
            if (strlen($row['password']) === 40 && password_verify($p, $row['password'])) {
                // Password matches, but it's an old SHA1 hash. Re-hash and update.
                $new_hash = password_hash($p, PASSWORD_DEFAULT);
                $update_q = "UPDATE admin SET password = ? WHERE user_id = ?";
                $update_stmt = mysqli_prepare($dbc, $update_q);
                mysqli_stmt_bind_param($update_stmt, 'si', $new_hash, $row['user_id']);
                mysqli_stmt_execute($update_stmt);
                // Optionally check for update success or log an error
                error_log("Re-hashed password for admin user ID: " . $row['user_id']);
            } else if (password_verify($p, $row['password'])) {
                 // Password matches the modern hash. No re-hashing needed.
            } else {
                // Password does not match
                $errors[] = 'The email address and password entered do not match.';
                // Consider adding brute-force protection logic here
            }

            // If there are no password verification errors, return success
            if(empty($errors)){
                 // Return true and the record (excluding the password hash)
                unset($row['password']);
                return array(true, $row);
            }
			
		} else { 
            // Email not found or multiple users with the same email (shouldn't happen if email is unique)
			$errors[] = 'The email address and password entered do not match.';
            // Consider adding brute-force protection logic here (for invalid emails)
		}
		
	} // End of empty($errors) IF.
	
	// Return false and the errors:
	return array(false, $errors);

} // End of check_login() function.
