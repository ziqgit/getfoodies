<html>
	<head><link rel="stylesheet" href="includes/loggedin.css" type="text/css" media="screen"/></head>
</html>

<?php 
// The user is redirected here from login.php.

session_start(); // Start the session.

// If no session value is present, redirect the user:
if (!isset($_SESSION['user_id'])) {

	// Need the functions:
	require ('includes/login_functions.inc.php');
	redirect_user();	

}

// Set the page title and include the HTML header:
$page_title = 'Logged In!';
include ('includes/header.html');

require('mysqli_connect.php');

?>

<div class="wrapper">

<?php
// Print a customized message:
echo "<h1>Logged In!</h1>
<p>You are now logged in, {$_SESSION['name']}!</p>";
//<p><a href=\"logout.php\">Logout</a></p>
?>
<a href="view_customer.php"><p ><input class="btn btn-lg btn-primary btn-block" type="submit" name="submit" value="View Customer" /></p></a>

</div>
<div class="background-slider"></div>
<?php
include ('includes/footer_loggedin.html');
?>

<style>
/* TEMPORARY: Email Management Styles - REMOVE AFTER TESTING */
.email-management {
	margin-top: 30px;
	padding: 20px;
	background: #fff;
	border-radius: 5px;
	box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.warning-banner {
	background-color: #fff3cd;
	border: 1px solid #ffeeba;
	color: #856404;
	padding: 15px;
	margin-bottom: 20px;
	border-radius: 4px;
	text-align: center;
}

.warning-banner strong {
	display: block;
	margin-bottom: 10px;
	font-size: 1.1em;
}

.email-table {
	width: 100%;
	border-collapse: collapse;
	margin: 10px 0 20px 0;
}

.email-table th, .email-table td {
	padding: 8px;
	text-align: left;
	border: 1px solid #ddd;
}

.email-table th {
	background-color: #f5f5f5;
}

.form-group {
	margin: 15px 0;
	padding: 15px;
	background: #f9f9f9;
	border-radius: 4px;
}

.form-group label {
	display: block;
	margin: 5px 0;
}

.form-group input {
	width: 100%;
	padding: 8px;
	margin: 5px 0;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.alert {
	padding: 10px;
	margin: 10px 0;
	border-radius: 4px;
}

.alert-success {
	background-color: #dff0d8;
	border: 1px solid #d6e9c6;
	color: #3c763d;
}
</style>

