<?php
require_once "../utils.php";
setup_checker();
session_start();
loginSecurity();
require_permission("system_smtp");

try {
    $db = new SQLite3($db_file);
    $db->busyTimeout(10000);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

if (isset($_POST["submit_verification"])){

    $teacher_id = $_SESSION["teacher_id"];
    $code_user = trim($_POST["verification_code"]);

    if (!ctype_digit($code_user) || strlen($code_user) != 6) {
        $_SESSION["error"] = "Invalid verification code";
        $db->close();
        header("Location: /NextStep/settings/verification.php");
        exit();
    }

    $query = "SELECT verification_code, verification_status FROM SMTP WHERE teacher_id = :id";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing insert query", $db->lastErrorMsg());
    }
    
    $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);

    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if (!$result) {
        errorMessages("Error executing insert query", $db->lastErrorMsg());
    }
    $code_db = $result["verification_code"];
    $status = $result["verification_status"];

    if ($status == EMAIL_VERIFIED) {
        $_SESSION["success"] = "This smtp settings are already verified";
        $db->close();
        header("Location: /NextStep/");
        exit();
    }
    
    if ($code_user != $code_db) {
        $_SESSION["error"] = "Wrong verification code";
        $db->close();
        header("Location: /NextStep/settings/verification.php");
        exit();
    }
  
    // Successfull verification
    $query = "UPDATE SMTP SET verification_status = :status WHERE teacher_id = :id";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing insert query", $db->lastErrorMsg());
    }
    
    $stmt->bindValue(":status", EMAIL_VERIFIED, SQLITE3_INTEGER);
    $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        $_SESSION["success"] = "Updated verification status successfully";
    } else {
        $_SESSION["error"] = "Failed to update verification status";
    }

    $db->close(); 
    header("Location: /NextStep/settings/?tab=email");
    exit();
}

$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);
$db->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="../css/style_navbar.css"/>
<link rel="stylesheet" href="../css/style_page.css"/>
<title>NextStep - Verification</title>
</head>
<body class="theme-<?= $color_theme ?>">
<?php include "../navbar.php"; ?>
<div class="page-box">
    <?php flashMessages(); ?>
    <h2>Verify your email</h2>
    <p>A 6-digit code has been sent to your SMTP email. Enter it below to confirm your settings.</p>
    <br/>
    <form method="POST">
        <input 
            type="number" 
            name="verification_code" 
            placeholder="000000" 
            autofocus>
        <br/>
        <button type="submit" class="simple-btn" name="submit_verification">Verify</button>
    </form>
</div>
<script src="../js/script.js"></script>
</body>
</html>