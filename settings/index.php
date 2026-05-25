<?php
session_start();
require_once "../utils.php";
loginSecurity();

$teacher_id = $_SESSION["teacher_id"];

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

# Fetch teacher info
$query =
    "SELECT teacher_name, teacher_email FROM TEACHERS WHERE teacher_id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
$result = $stmt->execute()->fetchArray();

$teacher_name = htmlspecialchars($result["teacher_name"]);
$teacher_email = htmlspecialchars($result["teacher_email"]);

# Get help from the theme helper
$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);
$db->close();

$tab = $_GET["tab"] ?? "general";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" type="image/x-icon" href="../images/logo.webp"/>
    <link rel="stylesheet" href="../css/style_navbar.css"/>
    <link rel="stylesheet" href="../css/style_page.css"/>
    <title>NextStep - Settings</title>
</head>
<body class="theme-<?= $color_theme ?>">

<?php include "../navbar.php"; ?>

<div class="settings-container">

    <!-- Tabs -->
    <div class="tabs">

        <!-- Everyone has access to the general tab -->
        <a href="?tab=general"><button class="tab <?= $tab === "general"
            ? "active"
            : "" ?>">General</button></a>

        <!-- Records tab -->
        <?php if (has_permission("system_records")): ?>
        <a href="?tab=records"><button class="tab <?= $tab === "records"
            ? "active"
            : "" ?>">Records</button></a>
        <?php endif; ?>

        <!-- System tab -->
        <?php if (has_permission("system_management")): ?>
        <a href="?tab=system_management"><button class="tab <?= $tab ===
        "system_management"
            ? "active"
            : "" ?>">System Management</button></a>
        <?php endif; ?>

        <!-- Everyone has a preferences tab -->
        <a href="?tab=preferences"><button class="tab <?= $tab === "preferences"
            ? "active"
            : "" ?>">Preferences</button></a>

    </div>

    <div class="tab-content active">
        <?php include __DIR__ . "/$tab.php"; ?>
    </div>

</div>

<script src="../js/script.js"></script>
</body>
</html>
