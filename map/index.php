<?php
require_once "../utils.php";
setup_checker();
session_start();
loginSecurity();
require_permission("view_students");

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="../css/style_navbar.css"/>
<link rel="stylesheet" href="../css/style_page.css"/>
<title>NextStep - Map </title>
</head>
<body class="theme-<?= $color_theme ?>">
<?php include "../navbar.php"; ?>
<?php flashMessages(); ?>
<script src="../js/script.js"></script>
</body>
</html>
