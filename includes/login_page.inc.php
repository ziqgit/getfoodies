<html>
<head>
    <style>
        .erorrh1 {
            color: red;
            text-align: center;
            font-size: 50px;
        }
        .errorp {
            color: red;
            text-align: center;
            font-size: 15px;
        }
        .error {
            color: red;
            font-size: 12px;
            margin-top: -10px;
            margin-bottom: 10px;
        }
        .form-control.error-border {
            border-color: red;
        }
    </style>
</head>
<body>
<?php 

// This page prints any errors associated with logging in
// and it creates the entire login page, including the form.

// Include the header:
$page_title = 'Login';
include ('includes/header.html');

// Retrieve any error messages from the session:
$email_error = $password_error = '';
$lockout_message = '';
$general_errors = [];
$all_errors_to_display = [];

if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
    foreach ($_SESSION['errors'] as $msg) {
        if (strpos($msg, 'locked') !== false || strpos($msg, 'Too many failed login attempts from your IP address') !== false) {
            $lockout_message = $msg; // Assign lockout message for prominent display
        } else {
            // Add all other errors to a single array for general display
            $all_errors_to_display[] = $msg;
        }
    }
    unset($_SESSION['errors']); // Clear the errors after displaying
}

?>

<div class="wrapper">
    <?php if (!empty($lockout_message)): ?>
        <div class="alert alert-danger" style="color: red; text-align: center; font-size: 16px; margin-bottom: 20px; padding: 10px; border: 1px solid red; background-color: #fff; border-radius: 5px;">
            <?php echo $lockout_message; ?>
        </div>
    <?php endif; ?>
    <form class="form-signin" action="login.php" method="post">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <h1 id="logo">Login</h1>
        <input type="text" class="form-control <?php echo !empty($email_error) ? 'error-border' : ''; ?>" placeholder="Email Address" name="email" size="20" maxlength="60" required/>
        <?php if (!empty($email_error)): ?>
            <!-- Removed specific email error display here to avoid duplication -->
        <?php endif; ?>
        <input type="password" class="form-control <?php echo !empty($password_error) ? 'error-border' : ''; ?>" placeholder="Password" name="pass1" size="20" maxlength="60" required/>
        <?php if (!empty($password_error)): ?>
            <!-- Removed specific password error display here to avoid duplication -->
        <?php endif; ?>
        <p><input class="btn btn-lg btn-primary btn-block" type="submit" name="submit" value="Login" /></p>
    </form>
</div>
<div class="background-slider"></div>

<?php 
// Display any collected error messages:
if (isset($all_errors_to_display) && !empty($all_errors_to_display)) {
    echo '<h1 id="erorrh1">Error!</h1>
    <p class="errorp">The following error(s) occurred:<br />';
    foreach ($all_errors_to_display as $msg) {
        echo " - $msg<br />\n";
    }
    echo '</p><p>Please try again.</p>';
}
?>

<?php include ('includes/footer_loggedin.html'); ?>
</body>
</html>
