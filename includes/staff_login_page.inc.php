<html>
<head>
    <link rel="stylesheet" href="includes/staff_login.css" type="text/css" media="screen"/>
</head>
<body>
<?php 

$page_title = 'Staff Login';
include ('includes/header.html');
$email_error = $password_error = '';
$lockout_message = '';

if (isset($_SESSION['staff_errors']) && !empty($_SESSION['staff_errors'])) {
    foreach ($_SESSION['staff_errors'] as $msg) {
        if (strpos($msg, 'locked') !== false) {
            $lockout_message = $msg;
        } elseif (strpos($msg, 'email') !== false) {
            $email_error = $msg;
        } elseif (strpos($msg, 'password') !== false) {
            $password_error = $msg;
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
            <div class="error"><?php echo $email_error; ?></div>
        <?php endif; ?>
        <input type="password" class="form-control <?php echo !empty($password_error) ? 'error-border' : ''; ?>" placeholder="Password" name="pass1" size="20" maxlength="60" required/>
        <?php if (!empty($password_error)): ?>
            <div class="error"><?php echo $password_error; ?></div>
        <?php endif; ?>
        <p><input class="btn btn-lg btn-primary btn-block" type="submit" name="submit" value="Login" /></p>
    </form>
</div>
<div class="background-slider"></div>
<?php include ('includes/footer_loggedin.html'); ?>
</body>
</html> 
