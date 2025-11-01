<?php
require_once "utils.php";
session_start();
loginSecurity();
if ($_SESSION["teacher_username"] != "ADMIN") {
    header("Location: index.php");
    exit();
}
check_id();

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
<label for="student_name">Name:</label>
<input type="text" id="student_name" value="<?=$student_name?>" />
<label for="student_email">Email:</label>
<input type="email" id="student_email" value="<?=$student_email?>" />
<label for="student_phone">Phone number:</label>
<input type="tel" id="student_phone" value="<?=$student_phone_number?>" />
<label for="class_name">Class name:</label>
<input type="text" id="class_name" value="<?=$class_name?>" />
<label for="country_name">Country:</label>
<input type="text" id="country_name" value="<?=$country_name?>" />
<label for="city_name">City:</label>
<input type="text" id="city_name" value="<?=$city_name?>" />
<label for="school_name">School:</label>
<input type="text" id="school_name" value="<?=$school_name?>" />
<label for="program_name">Program:</label>
<input type="text" id="program_name" value="<?=$program_name?>" />
<label for="status">Status:</label>
<input type="text" id="status" value="<?=$status?>" />
<label for="accessibility">Accessibility:</label>
<input type="text" id="accessibility" value="<?=$accessibility?>" />
<label for="created_date">Date created:</label>
<input type="text" id="created_date" value="<?=$created_date?>" />
<label for="last_update">Date last update:</label>
<input type="text" id="last_update" value="<?=$last_update?>" />
<input type="submit" class="nav-btn" name="save" value="Save">
</div>

</body>
</html>
