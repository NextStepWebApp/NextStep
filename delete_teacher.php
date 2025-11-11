<?php
require_once "utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

# This part checks who is getting deleted
$query = "SELECT teacher_username FROM TEACHERS WHERE teacher_id = :id";
$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing query", $db->lastErrorMsg());
}
$stmt->bindValue(":id", $_GET["teacher_id"], SQLITE3_INTEGER);
$result = $stmt->execute();
$teacher = $result->fetchArray();

if ($teacher["teacher_username"] == "ADMIN") {
    $_SESSION["error"] = "Cannot delete the ADMIN account";
    header("Location: teachers.php");
    exit();
}

# Main part of deletion
$query = "DELETE FROM TEACHERS WHERE teacher_id = :id";
$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing query", $db->lastErrorMsg());
}
$stmt->bindValue(":id", $_GET["teacher_id"], SQLITE3_INTEGER);
$results = $stmt->execute();
if (!$results) {
    errorMessages("Error executing query", $db->lastErrorMsg());
}
$db->close();
$_SESSION["success"] = "Teacher deleted successfully";
header("Location: teachers.php");
exit();
?>
