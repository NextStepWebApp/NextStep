<?php
require_once "utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);
$settings = download_page_settings();
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
    setTimeout(() => {
        window.location.href = "download.php";
    }, 1000); 
});
</script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="page-box">
    <?php flashMessages(); ?>
    
    <?php
     if ($settings == "teacher") {
         echo '<h2>Teacher Created Successfully</h2>';
         echo '<p>The credentials file will download automatically.';
         echo '<p>If it doesn’t, <a href="download.php">click here to download manually</a>.</p>';
         echo '<a href="teachers.php" class="simple-btn">View Teachers</a>';
     }
     if ($settings == "student") {
        echo '<h2>Downloading export csv file</h2>';
        echo '<p>The csv export file will download automatically.';
        echo '<p>If it doesn’t, <a href="download.php">click here to download manually</a>.</p>';
        echo '<a href="students.php" class="simple-btn">Back to Students</a>';
     }
     ?>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
