<?php

// See https://github.com/phpmailer/phpmailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

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

function query_students(SQLite3 $db, array $search, ?string $mode = "All") {
    $conditions = [];
    $params     = [];

    $like_fields = [
        "students_name",
        "students_email",
        "students_phone_number",
        "students_created_date",
    ];

    $name_fields = [
        "class_name"         => "class_name",
        "country_name"       => "country_name",
        "city_name"          => "city_name",
        "school_name"        => "school_name",
        "program_name"       => "program_name",
        "status_name"        => "status_name",
        "accessibility_name" => "accessibility_name",
    ];

    foreach ($like_fields as $field) {
        if (!empty($search[$field])) {
            $placeholder  = ":" . $field;
            $conditions[] = "STUDENTS.$field LIKE $placeholder";
            $params[$placeholder] = ["value" => "%" . $search[$field] . "%", "type" => SQLITE3_TEXT];
        }
    }

    foreach ($name_fields as $key => $column) {
        if (!empty($search[$key])) {
            $placeholder  = ":" . $key;
            $conditions[] = "$column = $placeholder";
            $params[$placeholder] = ["value" => $search[$key], "type" => SQLITE3_TEXT];
        }
    }

    $where_clause = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

    $joins = "JOIN STATUS            ON STUDENTS.students_status_id            = STATUS.status_id
              JOIN CLASS             ON STUDENTS.students_class_id             = CLASS.class_id
              JOIN COUNTRY           ON STUDENTS.students_country_id           = COUNTRY.country_id
              JOIN CITY              ON STUDENTS.students_city_id              = CITY.city_id
              JOIN SCHOOL            ON STUDENTS.students_school_id            = SCHOOL.school_id
              JOIN EDUCATION_PROGRAM ON STUDENTS.students_education_program_id = EDUCATION_PROGRAM.program_id
              JOIN ACCESSIBILITY     ON STUDENTS.students_accessibility_id     = ACCESSIBILITY.accessibility_id";

    if ($mode === "count") {
        $sql = "SELECT COUNT(*) as total FROM STUDENTS $joins $where_clause;";
    } elseif ($mode === "emails") {
        $sql = "SELECT students_name, students_email
                FROM STUDENTS $joins $where_clause
                ORDER BY STUDENTS.students_name ASC;";
    } else {
        // All
        $sql = "SELECT students_id, students_name, students_email,
                       students_created_date, status_name
                FROM STUDENTS $joins $where_clause
                ORDER BY STUDENTS.students_name ASC;";
    }

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        errorMessages("Error preparing student query", $db->lastErrorMsg());
    }

    foreach ($params as $key => $p) {
        $stmt->bindValue($key, $p["value"], $p["type"]);
    }

    $result = $stmt->execute();
    if (!$result) {
        errorMessages("Error executing student query", $db->lastErrorMsg());
    }

    if ($mode === "count") {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return (int) ($row["total"] ?? 0);
    }

    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

