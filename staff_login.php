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
    require ('includes/email_verification_functions.inc.php');
    
    // Secure Session Configuration
    session_start();
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'Strict');

    // Check the login:
    list ($check, $data) = check_staff_login($dbc, $_REQUEST['email'], $_REQUEST['pass1']);
    
    if ($check) { // Login successful
        // Generate and store verification code
        $code = generate_verification_code();
        if (store_verification_code($dbc, $data['id'], $code, false)) {
            // Get user's email
            $q = "SELECT email FROM staff WHERE id = ?";
            $stmt = mysqli_prepare($dbc, $q);
            mysqli_stmt_bind_param($stmt, 'i', $data['id']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                if (send_verification_email($row['email'], $code)) {
                    // Store temporary session data
                    $_SESSION['temp_user_id'] = $data['id'];
                    $_SESSION['temp_name'] = $data['name'];
                    $_SESSION['is_admin'] = false;
                    
                    // Redirect to verification page
                    header('Location: verify_email_code.php');
                    exit();
                }
            }
        }
        
        // If email verification fails, show error
        $errors[] = 'Failed to send verification email. Please try again.';
        $_SESSION['staff_errors'] = $errors;
        header("Location: staff_login.php");
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
