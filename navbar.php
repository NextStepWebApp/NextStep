<?php
# This is to prevent double session open, but to get the session open if accessed directly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "utils.php";
loginSecurity();
?>
<nav class="navbar">
<h1><a href="/NextStep/" class="brand-name">NextStep</a></h1>
<div class="nav-buttons">
<a href="/NextStep/settings" class="nav-btn">Settings</a>
<?php
if ($_SESSION["teacher_username"] == "ADMIN") {
    echo '<a href="/NextStep/teachers/" class="nav-btn">Teachers</a>';
}
?>
<?php
if ($_SESSION["teacher_username"] == "ADMIN") {
    echo '<a href="/NextStep/students/" class="nav-btn">Students</a>';
}
?>
<a href="/NextStep/map" class="nav-btn">Map</a>
<button class="nav-btn" data-open-modal>Log out</button>
<dialog data-modal id="logout-dialog">
    <h2>Confirm Logout</h2>
        <a href="/NextStep/logout.php" class="simple-btn">Yes, Log out</a>
        <button class="simple-btn" data-close-modal>Cancel</button>
</dialog>
</div>
</nav>
