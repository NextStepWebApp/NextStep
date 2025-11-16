<?php
require_once "utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);

$id = $_GET["student_id"];
check_id($id);

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

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
$_SESSION["success"] = "Student deleted successfully";
header("Location: index.php");
exit();
