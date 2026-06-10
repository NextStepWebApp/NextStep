<?php
# This is to prevent double session open, but to get the session open if accessed directly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "utils.php";
loginSecurity();
?>

<nav class="navbar">
<h1><a href="/NextStep/" class="brand-name"><?= $branding[
    "app_name"
] ?></a></h1>

<div class="nav-buttons">

<!-- OVERVIEW -->
<?php if (has_permission("view_students", $_SESSION["teacher_id"])): ?>
    <a href="/NextStep/overview" class="nav-btn">Overview</a>
<?php endif; ?>

<!-- STUDENTS -->
<?php if (has_permission("change_students", $_SESSION["teacher_id"])): ?>
    <a href="/NextStep/students/" class="nav-btn">Alumnus</a>
<?php endif; ?>

<!-- TEACHERS -->
<?php if (has_permission("teachers_access", $_SESSION["teacher_id"])): ?>
    <a href="/NextStep/teachers/" class="nav-btn">Teachers</a>
<?php endif; ?>

<!-- SETTINGS -->
<!-- everyone has access to a settings page -->
<a href="/NextStep/settings" class="nav-btn">Settings</a>

<!-- DATA -->
<?php if (has_permission("data_overview", $_SESSION["teacher_id"])): ?>
    <a href="/NextStep/data" class="nav-btn">Data</a>
<?php endif; ?>



<button class="nav-btn" data-open-modal>Log out</button>
<dialog data-modal id="logout-dialog">
    <h2>Confirm Logout</h2>
        <a href="/NextStep/logout.php" class="simple-btn">Yes, Log out</a>
        <button class="simple-btn" data-close-modal>Cancel</button>
</dialog>
</div>
</nav>
