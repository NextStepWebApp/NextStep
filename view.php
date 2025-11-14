<?php
require_once "utils.php";
session_start();
loginSecurity();
check_id($_GET["student_id"]);

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
$readable_date = date('Y-m-d H:i:s', $last_update);

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
<title>NextStep - View</title>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page-box">
<h2>Student information</h2>
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
<p>Date last update: <strong><?=$readable_date?></strong></p>
<?php if ($_SESSION["teacher_username"] == "ADMIN") { ?>
    <a href="edit.php?student_id=<?php echo $student_id; ?>" class="simple-btn">Edit</a>
    <a href="delete.php" class="simple-btn">Delete</a>
<?php } ?>
<a href="index.php#student-<?php echo $student_id; ?>" class="simple-btn">Back</a>
</div>
<script src="js/script.js"></script>
</body>
</html>
