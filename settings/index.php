<?php
require_once "../utils.php";
session_start();
loginSecurity();

$teacher_id = $_SESSION["teacher_id"];

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

# Fetch teacher info
$query = "SELECT teacher_name, teacher_email FROM TEACHERS WHERE teacher_id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
$result = $stmt->execute()->fetchArray();

$teacher_name = htmlspecialchars($result["teacher_name"]);
$teacher_email = htmlspecialchars($result["teacher_email"]);

$db->close();

$tab = $_GET['tab'] ?? 'general';
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
<body>

<?php include "../navbar.php"; ?>

<div class="settings-container">

    <!-- Tabs -->
    <div class="tabs">
        <a href="?tab=general"><button class="tab <?= $tab==='general'?'active':'' ?>">General</button></a>
        <a href="?tab=users"><button class="tab <?= $tab==='users'?'active':'' ?>">Users</button></a>
        <a href="?tab=data"><button class="tab <?= $tab==='data'?'active':'' ?>">Data</button></a>
        <a href="?tab=preferences"><button class="tab <?= $tab==='preferences'?'active':'' ?>">Preferences</button></a>
        <a href="?tab=advanced"><button class="tab <?= $tab==='advanced'?'active':'' ?>">Advanced</button></a>
    </div>

    <div class="tab-content active">
        <?php include __DIR__ . "/$tab.php"; ?>
    </div>

</div>

<script src="../js/script.js"></script>
</body>
</html>
