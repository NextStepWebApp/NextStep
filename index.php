<?php
session_start();
require_once "utils.php";
setup_checker();
if (!isset($_SESSION["teacher_username"])) {
    header("Location: login.php");
    exit();
}

try {
    $db = new SQLite3($db_file);
    $db->busyTimeout(10000);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

// Get total alumni
$result = $db->query("SELECT COUNT(*) FROM STUDENTS");
if (!$result) {
    errorMessages("Error with select query", $db->lastErrorMsg());
}
$row = $result->fetchArray(SQLITE3_NUM);
$total_alumni = $row ? $row[0] : 0;

// Get total teachers
$result = $db->query("SELECT COUNT(*) FROM TEACHERS");
if (!$result) {
    errorMessages("Error with select query", $db->lastErrorMsg());
}
$row = $result->fetchArray(SQLITE3_NUM);
$total_teachers = $row ? $row[0] : 0;

$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="css/style_navbar.css"/>
<link rel="stylesheet" href="css/style_page.css"/>
<title>NextStep - Home</title>
<style>
  
</style>
</head>
<body class="theme-<?= $color_theme ?>">
<?php include "navbar.php"; ?>
<?php flashMessages(); ?>

<div class="home-wrapper">

    <div class="home-header">
        <h1>NextStep</h1>
        <p>Alumni Tracking Web Application</p>
    </div>

    <div class="home-stats">
        <div class="home-stat">
            <div class="home-stat-number"><?= $total_alumni ?></div>
            <div class="home-stat-label">Alumni</div>
        </div>
        <div class="home-stat">
            <div class="home-stat-number"><?= $total_teachers ?></div>
            <div class="home-stat-label">Teachers</div>
        </div>
    </div>

    <div class="home-links">
        <a href="https://github.com/NextStepWebApp/NextStep" target="_blank" class="home-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-github" viewBox="0 0 16 16">
            <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 
            1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 
            2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8"/>
            </svg>
            GitHub
        </a>
        <a href="https://www.youtube.com/@MelchizedekShah" target="_blank" class="home-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-youtube" viewBox="0 0 16 16">
            <path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 
            1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.01 2.01 0 0 
            1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.01 2.01 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31 
            31 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.01 2.01 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A100 100 0 0 1 7.858 2zM6.4 5.209v4.818l4.157-2.408z"/>
            </svg>
            YouTube
        </a>
    </div>

    <div class="home-version">NextStep v1.0</div>

</div>

<script src="js/script.js"></script>
</body>
</html>