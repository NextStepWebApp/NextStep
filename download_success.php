<?php
require_once "utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);

if (!isset($_SESSION["new_teacher_credentials"]) || !isset($_SESSION["new_teacher_filename"])) {
    header("Location: create_teacher.php");
    exit();
}
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
<title>NextStep - Download</title>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Trigger automatic download after short delay
    setTimeout(() => {
        window.location.href = "download.php";
    }, 1000); // 1 second delay
});
</script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="page-box">
    <?php flashMessages(); ?>
    <h2>Teacher Created Successfully</h2>
    <p>The credentials file will download automatically. 
    If it doesn’t, <a href="download.php">click here to download manually</a>.</p>
    <a href="teachers.php" class="nav-btn">View Teachers</a>
    </div>
</body>
</html>
