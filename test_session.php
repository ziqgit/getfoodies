<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_set_cookie_params([
    'httponly' => true,
    'secure' => false,
    'samesite' => 'Lax',
    // 'domain' => '.getfoodies.website', // <-- comment this out for the test
    'path' => '/',
]);
session_start();
if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = 'hello';
    echo "Session set. Reload the page.";
} else {
    echo "Session value: " . $_SESSION['test'];
}
?>
