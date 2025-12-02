<?php
session_start();
require_once "../utils.php";
$settings = download_page_settings(); # Security checks are in this function
?>
<!DOCTYPE html>
<html lang="en">
<head>
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="../css/style_navbar.css"/>
<link rel="stylesheet" href="../css/style_page.css"/>
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
    <?php 
    # Only show navbar if not admin onboarding
    if ($settings !== "admin") {
        include "../navbar.php"; 
    }
    ?>
    <div class="page-box">
    <?php flashMessages(); ?>
    
    <?php
    if ($settings == "teacher") {
         echo '<h2>Teacher Created Successfully</h2>';
         echo '<p>The credentials file will download automatically.';
         echo '<p>If it doesn’t, <a href="download.php">click here to download manually</a>.</p>';
         echo '<a href="/NextStep/teachers" class="simple-btn">View Teachers</a>';
    }
    if ($settings == "student") {
        echo '<h2>Downloading export csv file</h2>';
        echo '<p>The csv export file will download automatically.';
        echo '<p>If it doesn’t, <a href="download.php">click here to download manually</a>.</p>';
        echo '<a href="/NextStep/students" class="simple-btn">Back to Students</a>';
    }
    if ($settings == "admin") {
         echo '<h2>Downloading ADMIN credentials</h2>';
         echo '<p>The ADMIN credentials file will download automatically.';
         echo '<p>If it doesn’t, <a href="download.php">click here to download manually</a>.</p>';
         echo '<a href="/NextStep" class="simple-btn">Take the NextStep</a>';
    } 
    ?>
    </div>
    <?php 
    # Only get js if not admin onboarding
    if ($settings !== "admin") {
        echo '<script src="js/script.js"></script>';
    }
    ?>
</body>
</html>
