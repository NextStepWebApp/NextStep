<?php

# This is temporary location
$db_file = "setup/nextstep_data.db";

function loginSecurity()
{
    if (!isset($_SESSION["teacher_username"])) {
        $_SESSION["error"] = "You are not logged in, please log in";
        header("Location: login.php");
        exit();
    }
}

function flashMessages()
{
    if (isset($_SESSION["error"])) {
        echo '<p class="flash" style="color: red;">' .
            htmlentities($_SESSION["error"]) .
            "</p>\n";
        unset($_SESSION["error"]);
    }
    if (isset($_SESSION["success"])) {
        echo '<p class="flash" style="color: green;">' .
            htmlentities($_SESSION["success"]) .
            "</p>\n";
        unset($_SESSION["success"]);
    }
}

function errorMessages(string $message, string $details)
{
    error_log($message . ": " . $details);
    $_SESSION["error"] = "$message";
    header("Location: failure.php");
    exit();
}


# Funtion that checks if the student_id is valid ( used in view, edit and delete)
function check_id() {
    if (!isset($_GET['student_id'])) {
        $_SESSION['error'] = "Missing student_id";
        header("Location: index.php");
        exit();
    }
    if (!is_numeric($_GET['student_id'])) {
        $_SESSION['error'] = "Invalid value for student_id";
        header("Location: index.php");
        exit();
    }
}


function full_students_database_query($db_file) {
    try {
        $db = new SQLite3($db_file);
    } catch (Exception $e) {
        errorMessages("Database connection failed", $e->getMessage());
    }

    $query = "SELECT
    STUDENTS.students_id,
    STUDENTS.students_name,
    STUDENTS.students_email,
    STUDENTS.students_phone_number,
    CLASS.class_name,
    COUNTRY.country_name,
    CITY.city_name,
    SCHOOL.school_name,
    EDUCATION_PROGRAM.program_name,
    STATUS.status_name,
    ACCESSIBILITY.accessibility_name,
    STUDENTS.students_created_date,
    STUDENTS.students_last_updated
    FROM STUDENTS
    LEFT JOIN CLASS ON STUDENTS.students_class_id = CLASS.class_id
    LEFT JOIN COUNTRY ON STUDENTS.students_country_id = COUNTRY.country_id
    LEFT JOIN CITY ON STUDENTS.students_city_id = CITY.city_id
    LEFT JOIN SCHOOL ON STUDENTS.students_school_id = SCHOOL.school_id
    LEFT JOIN EDUCATION_PROGRAM ON STUDENTS.students_education_program_id = EDUCATION_PROGRAM.program_id
    LEFT JOIN STATUS ON STUDENTS.students_status_id = STATUS.status_id
    LEFT JOIN ACCESSIBILITY ON STUDENTS.students_accessibility_id = ACCESSIBILITY.accessibility_id
    WHERE STUDENTS.students_id = :id;";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing query", $db->lastErrorMsg());
    }

    $stmt->bindValue(":id", $_GET["student_id"], SQLITE3_INTEGER);

    $results = $stmt->execute();
    if (!$results) {
        errorMessages("Error executing query", $db->lastErrorMsg());
    }
    $row = $results->fetchArray();
    $db->close();

    return $row;
}

function get_foreign_key(SQLite3 $db, string $provided_query, string $table_name) {
    $query = $provided_query;
    $query = "SELECT class_id FROM CLASS WHERE class_name = :class_name";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing query", $db->lastErrorMsg());
    }
    $stmt->bindValue(":table_name", $table_name, SQLITE3_TEXT);
    $result = $stmt->execute();
    if (!$result) {
        errorMessages("Error executing query", $db->lastErrorMsg());
    }
    $row = $result->fetchArray();
    $foreign_key = $row[0];
    return $foreign_key;
}


function super_user_privilages(string $super_teacher) {
    if ($super_teacher != "ADMIN") {
        header("Location: index.php");
        exit();
    }
}
