<?php
require_once "/srv/http/NextStep/utils.php";
require_once "/srv/http/NextStep/mailer.php";
setup_checker();

// Check internet connection 
if (!is_connected()) {
    error_log("No internet connection", 0);
    exit();
}


try {
    $db = new SQLite3($db_file);
    $db->busyTimeout(20000);
} catch (Exception $e) {
    error_log($db->lastErrorMsg(), 0);
    exit();
}

$query = "SELECT STUDENTS.students_id, STUDENTS.students_name, STUDENTS.students_email,
    TEACHERS.teacher_id, TEACHERS.teacher_name, EMAIL_DATA.data_id, EMAIL_DATA.data_email_type, 
    EMAIL_DATA.data_email_subject, EMAIL_DATA.data_email_body, EMAIL_DATA.data_email_filter,
    EMAIL_QUEUE.queue_id, EMAIL_QUEUE.queue_attempts, EMAIL_QUEUE.queue_created_at 
    FROM EMAIL_QUEUE 
    JOIN STUDENTS ON EMAIL_QUEUE.students_id = STUDENTS.students_id
    JOIN TEACHERS ON EMAIL_QUEUE.teacher_id = TEACHERS.teacher_id
    JOIN EMAIL_DATA ON EMAIL_QUEUE.data_id = EMAIL_DATA.data_id
    ORDER BY EMAIL_QUEUE.queue_created_at ASC, EMAIL_QUEUE.queue_attempts ASC
    LIMIT 10;";

$stmt = $db->prepare($query);
if (!$stmt) {
    error_log($db->lastErrorMsg(), 0);
    exit();
}
    
$result = $stmt->execute();
if (!$result) {
    error_log($db->lastErrorMsg(), 0);
    exit();
}


$found = false;
while ($row_queue = $result->fetchArray(SQLITE3_ASSOC)) {
    $found = true;

    // Get the SMTP settings from the teacher in the queu
    $query = "SELECT smtp_email, smtp_host, smtp_port, 
    smtp_password, verification_status FROM SMTP
    WHERE teacher_id = :id";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        error_log($db->lastErrorMsg(), 0);
    }

    $stmt->bindValue(":id", $row_queue["teacher_id"], SQLITE3_INTEGER);

    $result_SMTP = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if (!$result_SMTP) {
        // not supposed to happen, because there are checks 
        // for this before sending to queue
        error_log("Teacher does not have smtp settings", 0);
    }

    $smtp_host = $result_SMTP["smtp_host"];
    $smtp_email = $result_SMTP["smtp_email"];
    $smtp_password = crypto_decrypt($result_SMTP["smtp_password"], sys_get_node_status());
    $smtp_port = $result_SMTP["smtp_port"];
    $smtp_username = $row_queue["teacher_name"];
    $smtp_recever = $row_queue["students_email"];
    $smtp_recever_username = $row_queue["students_name"];
    $mail_subject = $row_queue["data_email_subject"];
    $mail_template = "templates/alumni.php";
    $mail_body = $row_queue["data_email_body"];
    $verification_code = null;
    $school_name = $branding["school_name"];

    // Check the email type
    $email_type = $row_queue["data_email_type"];
    $alumni_data = [];
    if ($email_type == EMAIL_FULL) {
        // Select full alumni data
        $query = "SELECT STUDENTS.students_name, STUDENTS.students_email,
            CLASS.class_name, COUNTRY.country_name, CITY.city_name,
            SCHOOL.school_name, EDUCATION_PROGRAM.program_name, STATUS.status_name, ACCESSIBILITY.accessibility_name,
            STUDENTS.students_company, STUDENTS.students_job_title,
            STUDENTS.students_linkedin_url, STUDENTS.students_website, STUDENTS.students_bio,
            STUDENTS.students_created_date, STUDENTS.students_last_updated
            FROM STUDENTS
            JOIN CLASS ON STUDENTS.students_class_id = CLASS.class_id
            JOIN COUNTRY ON STUDENTS.students_country_id = COUNTRY.country_id
            JOIN CITY ON STUDENTS.students_city_id = CITY.city_id
            JOIN SCHOOL ON STUDENTS.students_school_id = SCHOOL.school_id
            JOIN EDUCATION_PROGRAM ON STUDENTS.students_education_program_id = EDUCATION_PROGRAM.program_id
            JOIN STATUS ON STUDENTS.students_status_id = STATUS.status_id
            JOIN ACCESSIBILITY ON STUDENTS.students_accessibility_id = ACCESSIBILITY.accessibility_id
            WHERE STUDENTS.students_id = :id;";

        $stmt = $db->prepare($query);
        if (!$stmt) {
            error_log($db->lastErrorMsg(), 0);
            exit();
        }

        $stmt->bindValue(":id", $row_queue["students_id"], SQLITE3_INTEGER);

        $alumni_data = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        if (!$alumni_data) {
            error_log("Alumni does not exist", 0);
        }
    } 

    // Gets real functionality when NextStep-Cloud is made
    $update_url = "https://github.com/NextStepWebApp/NextStep-Cloud";
    $filters = $row_queue["data_email_filter"];

    //function mail_sender(string $smtp_host, string $smtp_email, 
    //string $smtp_password, int $smtp_port, string $smtp_username,
    //string $smtp_recever, string $smtp_recever_username,
    //string $mail_subject, string $mail_template, string $mail_body,
    //int $verification_code, string $school_name, array $alumni_data,
    //string $update_url, array $filters) 

    $mail = mail_sender($smtp_host, $smtp_email, $smtp_password, $smtp_port, $smtp_username,
        $smtp_recever, $smtp_recever_username,$mail_subject, $mail_template, $mail_body,
        $verification_code, $school_name, $alumni_data, $update_url, $filters);
    
    if ($mail["status"]) {
        save_to_log($db, $row_queue, $mail, EMAIL_SUCCESS); 

    } elseif (!$mail["status"] && $row_queue["queue_attempts"] < QUEUE_LIMIT) {
        // Add 1 to queue attempts
        $query = "UPDATE EMAIL_QUEUE SET queue_attempts = queue_attempts + 1
            WHERE queue_id = :queue_id";
        $stmt = $db->prepare($query);
        if (!$stmt) {
            error_log($db->lastErrorMsg(), 0);
            exit();
        }
        $stmt->bindValue(":queue_id", $row_queue["queue_id"], SQLITE3_INTEGER);

        if (!$stmt->execute()) {
            error_log("Failed to add 1 to email queue, email did not send", 0);
            exit();
        } 
    } else {    
        // Move the queue to log as failed!
        save_to_log($db, $row_queue, $mail, EMAIL_FAILED); 
    }
}

if (!$found) {
    error_log("No queue available", 0);
    exit();
}


function save_to_log(SQLite3 $db, array $row_queue, array $mail, int $status) {
    $db->exec('BEGIN');

    $query = "INSERT INTO EMAIL_LOG (
            students_id,
            teacher_id,
            data_id,
            log_status,
            log_message,
            log_sent_at
            )
            VALUES (
                :student,
                :teacher,
                :data,
                :status,
                :message,
                :sent
            )"; 

    $stmt = $db->prepare($query);
    if (!$stmt) {
        error_log($db->lastErrorMsg(), 0);
    }

    $stmt->bindValue(":student", $row_queue["students_id"], SQLITE3_INTEGER);
    $stmt->bindValue(":teacher", $row_queue["teacher_id"], SQLITE3_INTEGER);
    $stmt->bindValue(":data", $row_queue["data_id"], SQLITE3_INTEGER);
    $stmt->bindValue(":status", $status, SQLITE3_INTEGER);
    $stmt->bindValue(":message", $mail["message"], SQLITE3_TEXT);
    $stmt->bindValue(":sent", date("Y-m-d H:i:s"), SQLITE3_TEXT);
        
    if (!$stmt->execute()) {
        if ($status == EMAIL_SUCCESS) {
            error_log("Failed to save email to log, but email did send", 0);
        } else {
            error_log("Failed to save email to log and email did not send", 0);
        }
    } 

    // Now delete the queue for this job
    $query = "DELETE FROM EMAIL_QUEUE WHERE queue_id = :id";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        error_log($db->lastErrorMsg(), 0);
    }
    $stmt->bindValue(":id", $row_queue["queue_id"], SQLITE3_INTEGER);

    if (!$stmt->execute()) {
        if ($status == EMAIL_SUCCESS) {
            error_log("Failed to delete successfull email from queue, email did send", 0);
        } else {
            error_log("Failed to delete failed email from queue, email did not send", 0);
        }
    }

    $db->exec('COMMIT');
}
