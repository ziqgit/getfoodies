<?php
// Redirect IP-based access to domain (optional but recommended)
if ($_SERVER['HTTP_HOST'] !== 'getfoodies.website') {
    header('Location: http://getfoodies.website' . $_SERVER['REQUEST_URI']);
    exit();
}

// Set security headers
header("X-Frame-Options: DENY");
header("Content-Security-Policy: frame-ancestors 'none';");

// Set secure session cookie parameters (apply to domain)
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'getfoodies.website',
    'secure' => false, // Set to true only when you enable HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("⚠️ CSRF validation failed! Request blocked.");
    }

    require('includes/login_functions.inc.php');
    require('mysqli_connect.php');

    list($check, $data) = check_login($dbc, $_POST['email'], $_POST['pass1']);

    if ($check) {
        // Set session variables securely
        $_SESSION['user_id'] = htmlspecialchars($data['user_id'], ENT_QUOTES, 'UTF-8');
        $_SESSION['name'] = htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8');
        session_regenerate_id(true);

        redirect_user('loggedin.php');

    } else {
        $errors = $data;
        $_SESSION['errors'] = htmlspecialchars(json_encode($errors), ENT_QUOTES, 'UTF-8');
        header("Location: login.php");
        exit();
    }

    mysqli_close($dbc);
}

// Show login form
include('includes/login_page.inc.php');
?>
