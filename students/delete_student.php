<?php
require_once "../utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);

check_id($_GET["student_id"], "Students");
$id = $_GET["student_id"];

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

# To get the name of the student
$query = "SELECT students_name FROM STUDENTS WHERE students_id = :id";
$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing query", $db->lastErrorMsg());
}
$stmt->bindValue(":id", $id, SQLITE3_INTEGER);
$results = $stmt->execute();
if (!$results) {
    errorMessages("Error executing query", $db->lastErrorMsg());
}
$result_name = $results->fetchArray();

# Check to see if it is a valid value
if (!$result_name) {
    $_SESSION["error"] = "Invalid value for student_id";
    header("Location: /NextStep/");
    exit();
}

$student_name = $result_name["students_name"];

# Delete part
$query = "DELETE FROM STUDENTS WHERE students_id = :id";
$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing query", $db->lastErrorMsg());
}
$stmt->bindValue(":id", $id, SQLITE3_INTEGER);
$results = $stmt->execute();
if (!$results) {
    errorMessages("Error executing query", $db->lastErrorMsg());
}
$db->close();
$_SESSION["success"] = "Student $student_name deleted successfully";

// Back url with search filters
parse_str($_SERVER["QUERY_STRING"], $params);
unset($params["student_id"]);

$redirect = "/NextStep/";
if (!empty($params)) {
    $redirect .= "?" . http_build_query($params);
}
header("Location: $redirect");
exit();
