<?php 
require 'config.php';
require 'vendor/autoload.php'; // For SendGrid

function sendMailSendGrid($email, $subject, $message) {
    $emailObj = new \SendGrid\Mail\Mail();
    $emailObj->setFrom(SEND_FROM, SEND_FROM_NAME);
    $emailObj->setSubject($subject);
    $emailObj->addTo($email);
    $emailObj->addContent("text/html", $message);
    $emailObj->addContent("text/plain", strip_tags($message));

    $sendgrid = new \SendGrid(PASSWORD); // PASSWORD is your API key from config.php
    try {
        $response = $sendgrid->send($emailObj);
        if ($response->statusCode() == 202) {
            return "success";
        } else {
            return "Email not sent. Status: " . $response->statusCode() . " Body: " . $response->body();
        }
    } catch (Exception $e) {
        return 'Email not sent. Exception: ' . $e->getMessage();
    }
}
?>
