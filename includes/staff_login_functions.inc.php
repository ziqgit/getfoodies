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
        $q = "SELECT id, name, password FROM staff WHERE email = ?";
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

            // Verify the password against the stored hash
            // SHA1 hashes are 40 characters long in hex representation
            if (strlen($stored_hash) === 40 && password_verify($p, $stored_hash)) {
                // Password matches, but it's an old SHA1 hash. Re-hash and update.
                $new_hash = password_hash($p, PASSWORD_DEFAULT);
                $update_q = "UPDATE staff SET password = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($dbc, $update_q);
                mysqli_stmt_bind_param($update_stmt, 'si', $new_hash, $row['id']);
                mysqli_stmt_execute($update_stmt);
                // Optionally check for update success or log an error
                 error_log("Re-hashed password for staff user ID: " . $row['id']);
            } else if (password_verify($p, $stored_hash)) {
                 // Password matches the modern hash. No re-hashing needed.
            } else {
                // Password does not match
                $errors[] = 'The email address and password entered do not match.';
                 // Consider adding brute-force protection logic here
            }

            // If there are no password verification errors, return success
            if(empty($errors)){
                 // Return true and the record (excluding the password hash)
                unset($row['password']); // Unset the original password field
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

} // End of check_staff_login() function. 
