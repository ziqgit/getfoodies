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
        $e = mysqli_real_escape_string($dbc, trim($email));
    }

    // Validate the password:
    if (empty($pass1)) {
        $errors[] = 'You forgot to enter your password.';
    } else {
        $p = mysqli_real_escape_string($dbc, trim($pass1)); 
    }

    if (empty($errors)) { // If everything's OK.
        // Retrieve the id and name for that email/password combination:
        $q = "SELECT id, name FROM staff WHERE email='$e' AND password=SHA1('$p')";
        $r = @mysqli_query ($dbc, $q); // Run the query.
        // Check the result:
        if (mysqli_num_rows($r) == 1) {
            // Fetch the record:
            $row = mysqli_fetch_array ($r, MYSQLI_ASSOC);
            // Return true and the record:
            return array(true, $row);
        } else { // Not a match!
            $errors[] = 'The email address and password entered do not match.';
        }
    }
    // Return false and the errors:
    return array(false, $errors);
} // End of check_staff_login() function. 