<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

function generate_verification_code() {
    // Generate a 6-digit code
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function send_verification_email($email, $verification_code) {
    $emailObj = new \SendGrid\Mail\Mail();
    $emailObj->setFrom(SEND_FROM, SEND_FROM_NAME);
    $emailObj->setSubject("Your Login Verification Code");
    
    // Create HTML message
    $html_message = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;'>
            <h2 style='color: #333;'>Login Verification Code</h2>
            <p>Your verification code is:</p>
            <div style='background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>
                {$verification_code}
            </div>
            <p>This code will expire in 5 minutes.</p>
            <p>If you didn't request this code, please ignore this email.</p>
            <hr style='border: 1px solid #eee; margin: 20px 0;'>
            <p style='color: #666; font-size: 12px;'>This is an automated message, please do not reply.</p>
        </div>
    </body>
    </html>";

    $emailObj->addTo($email);
    $emailObj->addContent("text/html", $html_message);
    $emailObj->addContent("text/plain", "Your verification code is: " . $verification_code);

    $sendgrid = new \SendGrid(PASSWORD);
    try {
        $response = $sendgrid->send($emailObj);
        return $response->statusCode() == 202;
    } catch (Exception $e) {
        error_log("SendGrid Error: " . $e->getMessage());
        return false;
    }
}

function store_verification_code($dbc, $user_id, $code, $is_admin = true) {
    $table = $is_admin ? 'admin' : 'staff';
    $id_field = $is_admin ? 'user_id' : 'id';
    
    // Store code and expiration time (5 minutes from now)
    $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    $q = "UPDATE $table SET 
          verification_code = ?, 
          verification_expires = ? 
          WHERE $id_field = ?";
    
    $stmt = mysqli_prepare($dbc, $q);
    mysqli_stmt_bind_param($stmt, 'ssi', $code, $expires, $user_id);
    return mysqli_stmt_execute($stmt);
}

function verify_code($dbc, $user_id, $code, $is_admin = true) {
    $table = $is_admin ? 'admin' : 'staff';
    $id_field = $is_admin ? 'user_id' : 'id';
    
    $q = "SELECT verification_code, verification_expires 
          FROM $table 
          WHERE $id_field = ? 
          AND verification_code = ? 
          AND verification_expires > NOW()";
    
    $stmt = mysqli_prepare($dbc, $q);
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        // Clear the verification code after successful verification
        $q = "UPDATE $table SET 
              verification_code = NULL, 
              verification_expires = NULL 
              WHERE $id_field = ?";
        $stmt = mysqli_prepare($dbc, $q);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        
        return true;
    }
    return false;
}
?> 
