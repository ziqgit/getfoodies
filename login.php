<?php
header("X-Frame-Options: DENY");
header("Content-Security-Policy: frame-ancestors 'none';");

// ✅ Set cookie parameters BEFORE session_start
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'getfoodies.website',
    'secure' => true,                // Set to true for HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

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
// Check if the form has been submitted:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("⚠️ CSRF validation failed! Request blocked.");
    }

    // Include helper files:
    require ('includes/login_functions.inc.php');
    require ('mysqli_connect.php');
    require ('includes/email_verification_functions.inc.php');

    // Get client IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Log IP and current failed attempts at the start of the request
    $initial_ip_check_q = "SELECT failed_attempts FROM ip_failed_logins WHERE ip_address = ?";
    $initial_ip_check_stmt = mysqli_prepare($dbc, $initial_ip_check_q);
    if ($initial_ip_check_stmt) {
        mysqli_stmt_bind_param($initial_ip_check_stmt, 's', $ip_address);
        mysqli_stmt_execute($initial_ip_check_stmt);
        $initial_ip_check_result = mysqli_stmt_get_result($initial_ip_check_stmt);
        if ($initial_ip_check_row = mysqli_fetch_assoc($initial_ip_check_result)) {
            error_log("login.php: Start of request for IP " . $ip_address . ". Initial failed attempts from DB: " . $initial_ip_check_row['failed_attempts']);
        } else {
            error_log("login.php: Start of request for IP " . $ip_address . ". IP not found in ip_failed_logins table initially.");
        }
    } else {
        error_log("login.php: Failed to prepare initial IP check statement: " . mysqli_error($dbc));
    }

    // Check the login:
    list($check, $data) = check_login($dbc, $_REQUEST['email'], $_REQUEST['pass1']);
    
    if ($check) { // Login successful

        // Generate and store verification code
        $code = generate_verification_code();
        error_log("Generated verification code for admin: " . $code);
        
        if (store_verification_code($dbc, $data['user_id'], $code, true)) {
            error_log("Stored verification code for admin in database");
            
            // Get user's email
            $q = "SELECT email FROM admin WHERE user_id = ?";
            $stmt = mysqli_prepare($dbc, $q);
            mysqli_stmt_bind_param($stmt, 'i', $data['user_id']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                 error_log("Found admin email: " . $row['email']);
                
                if (empty($row['email'])) {
                    $errors[] = 'No email address found for your admin account. Please contact administrator.';
                    $_SESSION['errors'] = $errors;
                    header("Location: login.php");
                    exit();
                }

                if (send_verification_email($row['email'], $code)) {
                    error_log("Verification email sent successfully for admin");

                    // Store temporary session data
                    $_SESSION['temp_user_id'] = $data['user_id'];
                    $_SESSION['temp_name'] = $data['name'];
                    $_SESSION['is_admin'] = true;

                    error_log("Admin session data stored. Redirecting to verification page...");

                    // Redirect to the verification page:
                    header('Location: verify_email_code.php');
                    exit();
                } else {
                    error_log("Failed to send verification email for admin");
                    $errors[] = 'Failed to send verification email. Please check your email address and try again.';
                    $_SESSION['errors'] = $errors;
                    header("Location: login.php");
                    exit();
                }
            } else {
                 error_log("No email found for admin user ID: " . $data['user_id']);
                $errors[] = 'No email address found for your admin account. Please contact administrator.';
                $_SESSION['errors'] = $errors;
                header("Location: login.php");
                exit();
            }
        } else {
            error_log("Failed to store verification code for admin in database");
            $errors[] = 'System error. Please try again later.';
            $_SESSION['errors'] = $errors;
            header("Location: login.php");
            exit();
        }

            
    } else { // Login failed

        $errors = $data;
        $_SESSION['errors'] = $errors;

        header("Location: login.php");
        exit();
    }

    mysqli_close($dbc); 

} // End of submit check

include ('includes/login_page.inc.php');
?>
