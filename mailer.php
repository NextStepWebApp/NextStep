<?php

// See https://github.com/phpmailer/phpmailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require "vendor/autoload.php";

function mail_sender(string $smtp_host, string $smtp_email, 
    string $smtp_password, int $smtp_port, string $smtp_username,
    string $smtp_recever, string $smtp_recever_username,
    string $mail_subject, string $mail_template, string $mail_body,
    ?int $verification_code, string $school_name, ?array $alumni_data,
    ?string $update_url, ?string $filters
) {

    $mail = new PHPMailer(true);

    try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();                                          
        $mail->Host       = $smtp_host; 
        $mail->SMTPAuth   = true;                                 
        $mail->Username   = $smtp_email;                   
        $mail->Password   = $smtp_password;                             
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
        $mail->Port       = $smtp_port;                                   
        $mail->Timeout    = 5;

        //Recipients
        $mail->setFrom($smtp_email, $smtp_username);
        $mail->addAddress($smtp_recever, $smtp_recever_username);
        $mail->addReplyTo($smtp_email);

        //Content
        $mail->isHTML(true);                                  
        $mail->Subject = $mail_subject;
        
        // Email body
        if (!defined("SECURE_ACCESS")) {
            define("SECURE_ACCESS", true);
        }
        ob_start();
        require $mail_template;
        $mail->Body = ob_get_clean();

        $mail->send();

        return [
            "status" => true,
            "message" => "Email sent successfully"
        ];

    } catch (Exception $e) {

        return [
            "status" => false,
            "message" => $mail->ErrorInfo
        ];
    }
}
