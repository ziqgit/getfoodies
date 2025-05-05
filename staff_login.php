<?php
header("X-Frame-Options: DENY");
header("Content-Security-Policy: frame-ancestors 'none';");
?>
<?php
session_start(); // Start session

// Generate CSRF Token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<?php 
// This page processes the staff login form submission.
// The script now uses sessions.

// Check if the form has been submitted:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("⚠️ CSRF validation failed! Request blocked.");
    }

    // Include helper files:
    require ('includes/staff_login_functions.inc.php');
    require ('mysqli_connect.php');
    
    // Secure Session Configuration
    session_start();
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'Strict');

    // Check the login:
    list ($check, $data) = check_staff_login($dbc, $_REQUEST['email'], $_REQUEST['pass1']);
    
    if ($check) { // Login successful
        // Set session variables securely:
        $_SESSION['staff_id'] = htmlspecialchars($data['id'], ENT_QUOTES, 'UTF-8');
        $_SESSION['staff_name'] = htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8');
        $_SESSION['staff_loggedin'] = true;
        session_regenerate_id(true);
        // Redirect to the staff dashboard:
        header('Location: staff_dashboard.php');
        exit();
    } else { // Login failed
        // Assign $data to $errors for staff_login_page.inc.php:
        $errors = $data;
        // Store errors in the session securely:
        $_SESSION['staff_errors'] = $errors;
        // Redirect back to the login form:
        header("Location: staff_login.php");
        exit();
    }
    // Close the database connection securely:
    mysqli_close($dbc); 
}
// Display the staff login page:
include ('includes/staff_login_page.inc.php');
?>
