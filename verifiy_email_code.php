<?php
session_start();

// Check if user has completed first step of login
if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: ' . ($_SESSION['is_admin'] ? 'login.php' : 'staff_login.php'));
    exit();
}

require('mysqli_connect.php');
require('includes/email_verification_functions.inc.php');

$page_title = 'Email Verification';
include('includes/header.html');

$error = '';
$user_id = $_SESSION['temp_user_id'];
$is_admin = $_SESSION['is_admin'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['code'])) {
        $code = trim($_POST['code']);
        
        if (verify_code($dbc, $user_id, $code, $is_admin)) {
            // Correct code, log them in
            $_SESSION[$is_admin ? 'user_id' : 'staff_id'] = $user_id;
            $_SESSION[$is_admin ? 'name' : 'staff_name'] = $_SESSION['temp_name'];
            $_SESSION[$is_admin ? 'loggedin' : 'staff_loggedin'] = true;
            
            // Clear temporary session data
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_name']);
            unset($_SESSION['is_admin']);
            
            // Redirect to appropriate dashboard
            header('Location: ' . ($is_admin ? 'loggedin.php' : 'staff_dashboard.php'));
            exit();
        } else {
            $error = 'Invalid or expired code. Please try again.';
        }
    }
}

// Handle resend code request
if (isset($_GET['resend'])) {
    $code = generate_verification_code();
    if (store_verification_code($dbc, $user_id, $code, $is_admin)) {
        // Get user's email
        $table = $is_admin ? 'admin' : 'staff';
        $id_field = $is_admin ? 'user_id' : 'id';
        $q = "SELECT email FROM $table WHERE $id_field = ?";
        $stmt = mysqli_prepare($dbc, $q);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            if (send_verification_email($row['email'], $code)) {
                $error = 'New verification code has been sent to your email.';
            } else {
                $error = 'Failed to send verification code. Please try again.';
            }
        }
    }
}
?>

<div class="wrapper">
    <form class="form-signin" action="verify_email_code.php" method="post">
        <h1 id="logo">Email Verification</h1>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <p class="message">Please enter the 6-digit verification code sent to your email.</p>
        
        <input type="text" class="form-control" name="code" placeholder="Enter 6-digit code" 
               pattern="[0-9]{6}" maxlength="6" required/>
        
        <p><input class="btn btn-lg btn-primary btn-block" type="submit" value="Verify" /></p>
        
        <p class="resend">
            Didn't receive the code? 
            <a href="verify_email_code.php?resend=1">Resend Code</a>
        </p>
    </form>
</div>

<?php include('includes/footer.html'); ?> 
