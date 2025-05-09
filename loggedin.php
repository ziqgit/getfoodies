<html>
	<head><link rel="stylesheet" href="includes/loggedin.css" type="text/css" media="screen"/></head>
</html>

<?php 
session_set_cookie_params([
    'httponly' => true,
    'secure' => false, // Set to true if using HTTPS
    'samesite' => 'Lax',
    'domain' => '.getfoodies.website',
    'path' => '/',
]);
ini_set('session.cookie_httponly', 1);
session_start(); // Start the session.

var_dump($_SESSION);
exit;

// The user is redirected here from login.php.

// If no session value is present, redirect the user:
if (!isset($_SESSION['user_id'])) {

	// Need the functions:
	require ('includes/login_functions.inc.php');
	redirect_user();	

}

// Set the page title and include the HTML header:
$page_title = 'Logged In!';
include ('includes/header.html');

?>

<div class="wrapper">

<?php
// Print a customized message:
echo "<h1>Logged In!</h1>
<p>You are now logged in, {$_SESSION['name']}!</p>";
//<p><a href=\"logout.php\">Logout</a></p>";
?>
<a href="view_customer.php"><p ><input class="btn btn-lg btn-primary btn-block" type="submit" name="submit" value="View Customer" /></p></a>

</div>
<div class="background-slider"></div>
<?php
include ('includes/footer_loggedin.html');
?>

