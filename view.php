<?php
require_once "utils.php";
session_start();
loginSecurity();

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

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

$query = "SELECT
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

if (!$row) {
    $_SESSION['error'] = 'Invalid value for student_id';
    header('Location: index.php');
    exit();
}

$student_name          = htmlspecialchars($row['students_name']);
$student_email         = htmlspecialchars($row['students_email']);
$student_phone_number  = htmlspecialchars($row['students_phone_number']);
$class_name            = htmlspecialchars($row['class_name']);
$country_name          = htmlspecialchars($row['country_name']);
$city_name             = htmlspecialchars($row['city_name']);
$school_name           = htmlspecialchars($row['school_name']);
$program_name          = htmlspecialchars($row['program_name']);
$status                = htmlspecialchars($row['status_name']);
$accessibility         = htmlspecialchars($row['accessibility_name']);
$created_date          = htmlspecialchars($row['students_created_date']);
$last_update           = htmlspecialchars($row['students_last_updated']);

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="css/style_navbar.css"/>
<link rel="stylesheet" href="css/style_view.css"/>
<title>NextStep - View</title>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page-box">
<h2>Student info</h2>
<p>Name: <strong><?=$student_name?></strong></p>
<p>Email: <strong><?=$student_email?></strong></p>
<p>Phone number: <strong><?=$student_phone_number?></strong></p>
<p>Class name: <strong><?=$class_name?></strong></p>
<p>Country: <strong><?=$country_name?></strong></p>
<p>City: <strong><?=$city_name?></strong></p>
<p>School: <strong><?=$school_name?></strong></p>
<p>Program: <strong><?=$program_name?></strong></p>
<p>Status: <strong><?=$status?></strong></p>
<p>Accessibility: <strong><?=$accessibility?></strong></p>
<p>Date created: <strong><?=$created_date?></strong></p>
<p>Date last update: <strong><?=$last_update?></strong></p>
<a href="edit.php" class="nav-btn">Edit</a>
<a href="delete.php" class="nav-btn">Delete</a>


</div>
</body>
</html>
