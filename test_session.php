<?php
session_set_cookie_params([
    'httponly' => true,
    'secure' => false, // true if using HTTPS
    'samesite' => 'Lax',
    'domain' => '.getfoodies.website',
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
