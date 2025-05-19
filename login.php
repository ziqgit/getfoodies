<?php
header("X-Frame-Options: DENY");
header("Content-Security-Policy: frame-ancestors 'none';");

// Set a custom session name before starting the session
session_name('GF_SESSID'); // Custom session name instead of default PHPSESSID

// ✅ Set cookie parameters BEFORE session_start
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'getfoodies.website',
    'secure' => false,                // Set to true for HTTPS
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

    // Check the login:
    list($check, $data) = check_login($dbc, $_REQUEST['email'], $_REQUEST['pass1']);
    
    if ($check) { // Login successful
        
        // Set session variables securely:
        $_SESSION['user_id'] = htmlspecialchars($data['user_id'], ENT_QUOTES, 'UTF-8');
        $_SESSION['name'] = htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8');
        $_SESSION['loggedin'] = true;
        
        // Regenerate session ID to prevent session fixation attacks:
        session_regenerate_id(true);

        // Redirect to the logged-in page:
        header('Location: loggedin.php');
        exit();
            
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
