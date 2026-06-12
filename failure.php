<?php
require_once "utils.php";
setup_checker();
session_start();
loginSecurity();

$message = $_SESSION["error"] ?? "Something went wrong";
$details = $_SESSION["error_details"] ?? "An unexpected error occurred. Please try again later.";

unset($_SESSION["error"]);
unset($_SESSION["error_details"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="css/style_navbar.css"/>
<link rel="stylesheet" href="css/style_page.css"/>
<title>NextStep - Error</title>
<style>
    .error-wrapper {
        max-width: 500px;
        margin: 150px auto 50px;
    }
  
    .error-message {
        font-size: 25px;
        font-weight: 600;
        color: var(--color-text-medium);
        margin-bottom: 30px;
        text-align: center;
    }
    .error-details {
        font-size: 13px;
        color: var(--color-text-muted);
        background: var(--color-bg-light);
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 15px;
        word-break: break-word;
        border: 1px solid var(--color-border-gray);
    }
</style>
</head>
<body class="theme-<?= $color_theme ?? "blue" ?>">
<?php include "navbar.php"; ?>

<div class="error-wrapper page-box">
    <div class="error-message"><?= htmlspecialchars($message) ?></div>
    <div class="error-details"><?= htmlspecialchars($details) ?></div>
    <div class="button-container" style="justify-content: center;">
        <a href="javascript:history.back()" class="simple-btn">Go Back</a>
    </div>
</div>

<script src="js/script.js"></script>
</body>
</html>