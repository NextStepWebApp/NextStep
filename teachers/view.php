<?php
require_once "../utils.php";
session_start();
loginSecurity();
check_id($_GET["teacher_id"], "Teacher");
require_permission("teachers_access"); 

try {
    $db = new SQLite3($db_file);
    $db->busyTimeout(10000);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);

$query = "SELECT TEACHERS.teacher_name, TEACHERS.teacher_username, 
    SMTP.smtp_email, SMTP.smtp_host, SMTP.smtp_port FROM TEACHERS 
    LEFT JOIN SMTP ON TEACHERS.teacher_id = SMTP.teacher_id
    WHERE TEACHERS.teacher_id = :teacher_id";

$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing query", $db->lastErrorMsg());
}

$teacher_id = $_GET["teacher_id"];
$stmt->bindValue(":teacher_id", $teacher_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if (!$row) {
    $_SESSION["error"] = "Teacher not found.";
    header("Location: /NextStep/teachers");
    exit();
}

$teacher_name = htmlspecialchars($row["teacher_name"]);
$teacher_username = htmlspecialchars($row["teacher_username"]);

if (empty($row["smtp_email"])) {
    $smtp_email = "Not configured";
    $smtp_host  = "Not configured";
    $smtp_port  = "Not configured";
} else {
    $smtp_email = htmlspecialchars($row["smtp_email"]);
    $smtp_host  = htmlspecialchars($row["smtp_host"]);
    $smtp_port  = htmlspecialchars($row["smtp_port"]);
}

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="../images/logo.webp"/>
<link rel="stylesheet" href="../css/style_navbar.css"/>
<link rel="stylesheet" href="../css/style_page.css"/>
<title>NextStep - View Email Settings</title>
</head>
<body class="theme-<?= $color_theme ?>">
<?php include "../navbar.php"; ?>

<div class="page-box">
<h2>Teacher Email Settings</h2>
<?php flashMessages(); ?>
<p>Name: <strong><?= $teacher_name ?></strong></p>
<p>Username: <strong><?= $teacher_username ?></strong></p>
<p>Email: <strong><?= $smtp_email ?></strong></p>
<p>Host: <strong><?= $smtp_host ?></strong></p>
<p>Port: <strong><?= $smtp_port ?></strong></p>

<a href="/NextStep/teachers/" class="simple-btn">Back</a>
</div>
<script src="../js/script.js"></script>
</body>
</html>