<?php
session_set_cookie_params([
    'httponly' => true,
    'secure' => false, // Set to true if using HTTPS
    'samesite' => 'Lax',
    'path' => '/',
]);
ini_set('session.cookie_httponly', 1);
session_start();
header("X-Frame-Options: DENY");
header("Content-Security-Policy: frame-ancestors 'none';");

// This page processes the login form submission.
// The script now uses sessions.

// Check if the form has been submitted:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Include helper files:
    require ('includes/login_functions.inc.php');
    require ('mysqli_connect.php');
    // Check the login:
    list ($check, $data) = check_login($dbc, $_REQUEST['email'], $_REQUEST['pass1']);
    if ($check) { // Login successful
        // Set session variables securely:
        $_SESSION['user_id'] = htmlspecialchars($data['user_id'], ENT_QUOTES, 'UTF-8');
        $_SESSION['name'] = htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8');
        // Regenerate session ID to prevent session fixation attacks:
        session_regenerate_id(true);
        // Redirect to the logged-in page:
        header("Location: loggedin.php");
        exit;
    } else { // Login failed
        // Assign $data to $errors for login_page.inc.php:
        $errors = $data;
        // Store errors in the session securely:
        $_SESSION['errors'] = htmlspecialchars(json_encode($errors), ENT_QUOTES, 'UTF-8');
        // Redirect back to the login form:
        header("Location: login.php");
        exit;
    }
    // Close the database connection securely:
    mysqli_close($dbc); 
} // End of the main submit conditional.
// Display the login page:
include ('includes/login_page.inc.php');
?>

