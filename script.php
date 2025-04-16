<?php 
/**
 * We have to put the PHPMailer namespaces at the top of the page.
 */
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/*
   We have to require the config.php file to use our 
   Gmail account login details.
 */
require 'config.php';

/*
   We have to require the path to the PHPMailer classes.
 */
require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

/**
 * The function uses the PHPMailer object to send an email 
 * to the address we specify.
 * @param  string $email    [Where our email goes]
 * @param  string $subject  [The email's subject]
 * @param  string $message  [The message]
 * @return string           [Error message, or success]
 */
function sendMail($email, $subject, $message){
   // Creating a new PHPMailer object.
   $mail = new PHPMailer(true);

   try {
       // Using the SMTP protocol to send the email.
       $mail->isSMTP();

       // Setting the SMTPAuth property to true to use 
       // Gmail login details to send the mail.
       $mail->SMTPAuth = true;

       // Setting the Host property to the MAILHOST value 
       // that we define in the config file.
       $mail->Host = MAILHOST;

       // Setting the Username property to the USERNAME value 
       // that we define in the config file.
       $mail->Username = USERNAME;

       // Setting the Password property to the PASSWORD value 
       // that we define in the config file.
       $mail->Password = PASSWORD;

       // Setting SMTPSecure to PHPMailer::ENCRYPTION_STARTTLS for encryption.
       $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

       // TCP port to connect with the Gmail SMTP server.
       $mail->Port = 587;

       // Who is sending the email. Using constants from the config file.
       $mail->setFrom(SEND_FROM, SEND_FROM_NAME);

       // Where the mail goes. Using the $email function's parameter.
       $mail->addAddress($email);

       // Where the recipient can reply to. Using constants from the config file.
       $mail->addReplyTo(REPLY_TO, REPLY_TO_NAME);

       // Setting email format to HTML.
       $mail->isHTML(true);

       // Assigning the incoming subject to the $mail->Subject property.
       $mail->Subject = $subject;

       // Assigning the incoming message to the $mail->Body property.
       $mail->Body = $message;

       // Providing a plain text alternative to the HTML version of the email.
       $mail->AltBody = strip_tags($message);

       // Sending the email.
       $mail->send();
       return "success";
   } catch (Exception $e) {
       // Returning error message if email not sent.
       return "Email not sent. Error: {$mail->ErrorInfo}";
   }
}
?>
