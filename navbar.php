<nav class="navbar">
<h1><a href="index.php" class="brand-name">NextStep</a></h1>
<div class="nav-buttons">
<a href="settings.php" class="nav-btn">Settings</a>
<a href="teachers.php" class="nav-btn">Teachers</a>
<?php
require_once "utils.php";
session_start();
loginSecurity();
# TEMPORARY !!!!!!!!!!!!!!!!!!!!!!!!!!!!!

# hotfix needed!!!!!
if ($_SESSION["teacher_username"] == "ADMIN") {
    echo '<a href="students.php" class="nav-btn">Students</a>';
}
?>
<a href="map.php" class="nav-btn">Map</a>
<a href="logout.php" class="nav-btn">Log out</a>
</div>
</nav>
