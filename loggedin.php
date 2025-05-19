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

// TEMPORARY: Email Management Functions - REMOVE AFTER TESTING
function update_email($dbc, $table, $id_field, $id, $new_email) {
	$q = "UPDATE $table SET email = ? WHERE $id_field = ?";
	$stmt = mysqli_prepare($dbc, $q);
	mysqli_stmt_bind_param($stmt, 'si', $new_email, $id);
	return mysqli_stmt_execute($stmt);
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['admin_id']) && isset($_POST['admin_email'])) {
		if (update_email($dbc, 'admin', 'user_id', $_POST['admin_id'], $_POST['admin_email'])) {
			$message = 'Admin email updated successfully.';
		}
	}
	if (isset($_POST['staff_id']) && isset($_POST['staff_email'])) {
		if (update_email($dbc, 'staff', 'id', $_POST['staff_id'], $_POST['staff_email'])) {
			$message = 'Staff email updated successfully.';
		}
	}
}

// Get current emails
$admin_emails = array();
$q = "SELECT user_id, email FROM admin";
$result = mysqli_query($dbc, $q);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	$admin_emails[] = $row;
}

$staff_emails = array();
$q = "SELECT id, email FROM staff";
$result = mysqli_query($dbc, $q);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	$staff_emails[] = $row;
}
?>

<div class="wrapper">

<?php
// Print a customized message:
echo "<h1>Logged In!</h1>
<p>You are now logged in, {$_SESSION['name']}!</p>";
//<p><a href=\"logout.php\">Logout</a></p>";
?>
<a href="view_customer.php"><p ><input class="btn btn-lg btn-primary btn-block" type="submit" name="submit" value="View Customer" /></p></a>

<!-- TEMPORARY: Email Management Section - REMOVE AFTER TESTING -->
<div class="email-management">
	<div class="warning-banner">
		<strong>⚠️ TEMPORARY TESTING TOOL - REMOVE AFTER TESTING ⚠️</strong>
		<p>This section is for testing email verification only. It will be removed after testing is complete.</p>
	</div>

	<h2>Email Management (Temporary)</h2>
	<?php if ($message): ?>
		<div class="alert alert-success"><?php echo $message; ?></div>
	<?php endif; ?>

	<div class="current-emails">
		<h3>Current Email Addresses</h3>
		
		<h4>Admin Emails:</h4>
		<table class="email-table">
			<tr>
				<th>User ID</th>
				<th>Email</th>
			</tr>
			<?php foreach ($admin_emails as $admin): ?>
			<tr>
				<td><?php echo $admin['user_id']; ?></td>
				<td><?php echo $admin['email']; ?></td>
			</tr>
			<?php endforeach; ?>
		</table>

		<h4>Staff Emails:</h4>
		<table class="email-table">
			<tr>
				<th>Staff ID</th>
				<th>Email</th>
			</tr>
			<?php foreach ($staff_emails as $staff): ?>
			<tr>
				<td><?php echo $staff['id']; ?></td>
				<td><?php echo $staff['email']; ?></td>
			</tr>
			<?php endforeach; ?>
		</table>
	</div>

	<div class="update-emails">
		<h3>Update Email Addresses</h3>
		<form method="post" class="email-form">
			<div class="form-group">
				<h4>Update Admin Email</h4>
				<label>Admin ID: <input type="number" name="admin_id" required></label><br>
				<label>New Email: <input type="email" name="admin_email" required></label>
			</div>
			
			<div class="form-group">
				<h4>Update Staff Email</h4>
				<label>Staff ID: <input type="number" name="staff_id" required></label><br>
				<label>New Email: <input type="email" name="staff_email" required></label>
			</div>
			
			<input type="submit" value="Update Emails" class="btn btn-lg btn-primary">
		</form>
	</div>
</div>

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

