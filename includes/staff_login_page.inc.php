<html>
<head>
    <link rel="stylesheet" href="includes/staff_login.css" type="text/css" media="screen"/>
</head>
<body>
<?php 

$page_title = 'Staff Login';
include_once ('includes/header.html');
$email_error = $password_error = '';
$lockout_message = '';
$general_errors = [];
$all_errors_to_display = [];
$match_error_message = '';

if (isset($_SESSION['staff_errors']) && !empty($_SESSION['staff_errors'])) {
    foreach ($_SESSION['staff_errors'] as $msg) {
        if (strpos($msg, 'locked') !== false || strpos($msg, 'Too many failed login attempts from your IP address') !== false) {
            $lockout_message = $msg;
        } else if ($msg === 'The email address and password entered do not match.') {
            $match_error_message = $msg;
        } else if (strpos($msg, 'email address') !== false) {
            $email_error = $msg;
        } else if (strpos($msg, 'password') !== false) {
            $password_error = $msg;
        } else {
            // Add all other errors to the display array
            $all_errors_to_display[] = $msg;
        }
    }
    unset($_SESSION['staff_errors']);
}
?>
<div class="wrapper">
    <?php if (!empty($lockout_message)): ?>
        <div class="alert alert-danger" style="color: red; text-align: center; font-size: 16px; margin-bottom: 20px; padding: 10px; border: 1px solid red; background-color: #fff; border-radius: 5px;">
            <?php echo $lockout_message; ?>
        </div>
    <?php endif; ?>
    <form class="form-signin" action="staff_login.php" method="post">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <h1 id="logo">Staff Login</h1>
        <input type="text" class="form-control <?php echo !empty($email_error) ? 'error-border' : ''; ?>" placeholder="Email Address" name="email" size="20" maxlength="60" required/>
        <?php if (!empty($email_error)): ?>
            <div class="error-message"><?php echo $email_error; ?></div>
        <?php endif; ?>
        <input type="password" class="form-control <?php echo !empty($password_error) ? 'error-border' : ''; ?>" placeholder="Password" name="pass1" size="20" maxlength="60" required/>
        <?php if (!empty($password_error)): ?>
            <div class="error-message"><?php echo $password_error; ?></div>
        <?php endif; ?>
        <p><input class="btn btn-lg btn-primary btn-block" type="submit" name="submit" value="Login" /></p>
    </form>
</div>
<div class="background-slider"></div>
<?php 
// Display the specific match error if it exists
if (!empty($match_error_message)) {
    echo '<p class="errorp">' . $match_error_message . '</p>';
}

// Display any other collected error messages in the container:
if (isset($all_errors_to_display) && !empty($all_errors_to_display)) {
    echo '<div class="error-container">
        <h2 class="error-title">Error!</h2>
        <div class="error-content">
            <p>The following error(s) occurred:</p>
            <ul class="error-list">';
    foreach ($all_errors_to_display as $msg) {
        echo "<li>$msg</li>\n";
    }
    echo '</ul>
            <p>Please try again.</p>
        </div>
    </div>';
}
?>
<?php include ('includes/footer_loggedin.html'); ?>
</body>
</html> 
