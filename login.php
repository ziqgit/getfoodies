<?php
header("X-Frame-Options: DENY");
header("Content-Security-Policy: frame-ancestors 'none';");

session_start(); // Start session

// Generate CSRF Token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<html>
<head>
    <link rel="stylesheet" href="includes/login.css" type="text/css" media="screen"/>
</head>
</html>

<?php 
// This page processes the login form submission.
// The script now uses sessions.

// Check if the form has been submitted:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("⚠️ CSRF validation failed! Request blocked.");
    }

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
        redirect_user('loggedin.php');
            
    } else { // Login failed

        // Assign $data to $errors for login_page.inc.php:
        $errors = $data;

        // Store errors in the session securely:
        $_SESSION['errors'] = htmlspecialchars(json_encode($errors), ENT_QUOTES, 'UTF-8');

        // Redirect back to the login form:
        header("Location: login.php");
        exit();
    }
    
    // Close the database connection securely:
    mysqli_close($dbc); 

} // End of the main submit conditional.

// Display the login page:
include ('includes/login_page.inc.php');
?>
