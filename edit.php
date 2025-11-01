<?php
require_once "utils.php";
session_start();
loginSecurity();
if ($_SESSION["teacher_username"] != "ADMIN") {
    header("Location: index.php");
    exit();
}
check_id();


// Handle form submission
if (isset($_POST["submit"])) {

    // Check if all fields are filled
    if (strlen($_POST["student_name"]) < 1 || strlen($_POST["student_email"]) < 1 ||
        strlen($_POST["student_phone"]) < 1 ||
        strlen($_POST["class_name"]) < 1 || strlen($_POST["country_name"]) < 1 ||
        strlen($_POST["city_name"]) < 1 || strlen($_POST["school_name"]) < 1 ||
        strlen($_POST["program_name"]) < 1 || strlen($_POST["status"]) < 1 ||
        strlen($_POST["accessibility"]) < 1) {

        $_SESSION['error'] = "All fields are required";
        header("Location: edit.php?student_id=" . $_POST['student_id']);
        exit();
    }
    $db = new SQLite3($db_file);
    // Update student information
    $query = "UPDATE STUDENTS SET
        students_name = :name,
        students_email = :email,
        students_phone_number = :phone,
        students_last_updated = NOW()
        WHERE students_id = :id";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing query", $db->lastErrorMsg());
    }

    $stmt->bindValue(":name", $_POST["student_name"], SQLITE3_TEXT);
    $stmt->bindValue(":email", $_POST["student_email"], SQLITE3_TEXT);
    $stmt->bindValue(":phone", $_POST["student_phone"], SQLITE3_TEXT);
    $stmt->bindValue(":id", $_POST["student_id"], SQLITE3_INTEGER);

    $results = $stmt->execute();
    if (!$results) {
        errorMessages("Error executing query", $db->lastErrorMsg());
    }
    $_SESSION['success'] = 'Student information updated successfully';
        header("Location: edit.php?student_id=" . $_POST['student_id']);
        exit();
}

# Get data from the db
# Funtion in utils
$row = full_students_database_query($db_file);

if (!$row) {
    $_SESSION['error'] = 'Invalid value for student_id';
    header('Location: index.php');
    exit();
}

$student_id            = htmlspecialchars($row['students_id']);
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



?>
<!DOCTYPE html>
<html lang="en">
<head>
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="css/style_navbar.css"/>
<link rel="stylesheet" href="css/style_page.css"/>
<title>NextStep - Settings</title>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="page-box">
<h2>Student information</h2>
<?php flashMessages(); ?>

<form method="POST" action="edit.php">
<input type="hidden" name="student_id" value="<?=$student_id?>" />
<label for="student_name">Name:</label>
<input type="text" id="student_name" name="student_name" value="<?=$student_name?>"/>
<label for="student_email">Email:</label>
<input type="email" id="student_email" name="student_email" value="<?=$student_email?>"/>
<label for="student_phone">Phone number:</label>
<input type="tel" id="student_phone" name="student_phone" value="<?=$student_phone_number?>"/>
<label for="class_name">Class name:</label>
<input type="text" id="class_name" name="class_name" value="<?=$class_name?>"/>
<label for="country_name">Country:</label>
<input type="text" id="country_name" name="country_name" value="<?=$country_name?>"/>
<label for="city_name">City:</label>
<input type="text" id="city_name" name="city_name" value="<?=$city_name?>"/>
<label for="school_name">School:</label>
<input type="text" id="school_name" name="school_name" value="<?=$school_name?>"/>
<label for="program_name">Program:</label>
<input type="text" id="program_name" name="program_name" value="<?=$program_name?>"/>
<label for="status">Status:</label>
<input type="text" id="status" name="status" value="<?=$status?>"/>
<label for="accessibility">Accessibility:</label>
<input type="text" id="accessibility" name="accessibility" value="<?=$accessibility?>"/>
<label for="created_date">Date created:</label>
<input type="text" id="created_date" name="created_date" value="<?=$created_date?>" readonly />
<label for="last_update">Date last update:</label>
<input type="text" id="last_update" name="last_update" value="<?=$last_update?>" readonly />
<input type="submit" class="nav-btn" name="submit" value="Save">
</form>
</div>
</body>
</html>
