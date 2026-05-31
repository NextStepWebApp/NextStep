<?php

// See https://github.com/phpmailer/phpmailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require "vendor/autoload.php";

function mail_sender(string $smtp_host, string $smtp_email, 
    string $smtp_password, int $stmt_port, string $smtp_username,
    string $smtp_recever, string $smtp_recever_username 

) {

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
        $mail->isSMTP();                                          
        $mail->Host       = $smtp_host; 
        $mail->SMTPAuth   = true;                                 
        $mail->Username   = $smtp_email;                   
        $mail->Password   = $smtp_password;                             
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
        $mail->Port       = $stmt_port;                                   

        //Recipients
        $mail->setFrom($smtp_email, $smtp_username);
        $mail->addAddress($smtp_recever, $smtp_recever_username);
        $mail->addReplyTo($smtp_email);

        //Content
        $mail->isHTML(true);                                  
        $mail->Subject = 'Here is the subject';
        $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        echo 'Message has been sent';

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
