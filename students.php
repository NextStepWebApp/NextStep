<?php
require_once "utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);
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
<title>NextStep - Student</title>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page-box">
<?php flashMessages(); ?>
<a href="create_student.php" class="simple-btn">Create Student</a>
<a href="import_students.php" class="simple-btn">Import Data</a>
<a href="export_students.php" class="simple-btn">Export Data</a>


</div>
<script src="js/script.js"></script>
</body>
</html>
