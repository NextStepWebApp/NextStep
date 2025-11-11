<?php
# This is to prevent double session open, but to get the session open if accessed directly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "utils.php";
loginSecurity();
?>
<nav class="navbar">
<h1><a href="index.php" class="brand-name">NextStep</a></h1>
<div class="nav-buttons">
<a href="settings.php" class="nav-btn">Settings</a>
<?php
if ($_SESSION["teacher_username"] == "ADMIN") {
    echo '<a href="teachers.php" class="nav-btn">Teachers</a>';
}
?>
<?php
if ($_SESSION["teacher_username"] == "ADMIN") {
    echo '<a href="students.php" class="nav-btn">Students</a>';
}
?>
<a href="map.php" class="nav-btn">Map</a>
<button class="nav-btn" data-open-modal>Log out</button>
<dialog data-modal id="logout-dialog">
    <h2>Confirm Logout</h2>
        <a href="logout.php" class="simple-btn">Yes, Log out</a>
        <button class="simple-btn" data-close-modal>Cancel</button>
</dialog>
</div>
</nav>
