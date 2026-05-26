<?php
require_once "../utils.php";
session_start();
loginSecurity();
require_permission("teachers_access");
check_id($_GET["teacher_id"], "Teacher");

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

$teacher_id = $_GET["teacher_id"];

# Handle form submission
if (isset($_POST["submit"])) {
    if (empty($_POST["teacher_username"])) {
        $_SESSION["error"] = "The username field cannot be empty";
        header("Location: /NextStep/teachers/edit_teacher.php?teacher_id=" . $teacher_id);
        exit();
    }

    # Validate username
    $teacher_username = trim($_POST["teacher_username"]);

    # Prevent changing ADMIN username
    if ($teacher_username == "ADMIN") {
        //$_SESSION["error"] = "Cannot change ADMIN username";
        header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
        exit();
    }

    validate_teacher_username($db, $teacher_username, "edit_teacher", $teacher_id);

    # Check if username already exists not looking at the current teacher
    $query = "SELECT teacher_id FROM TEACHERS WHERE teacher_username = :username AND teacher_id != :id";

    $stmt = $db->prepare($query);

    if (!$stmt) {
        errorMessages("Error preparing check query", $db->lastErrorMsg());
    }

    $stmt->bindValue(":username", $teacher_username, SQLITE3_TEXT);
    $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
    $result = $stmt->execute();

    if (!$result) {
        errorMessages("Error executing check query", $db->lastErrorMsg());
    }

    $existing = $result->fetchArray();

    if ($existing) {
        $_SESSION["error"] = "A teacher with this username already exists";
        header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
        $db->close();
        exit();
    }

    # Update teacher
    $query = "UPDATE TEACHERS SET teacher_username = :username WHERE teacher_id = :id";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing update query", $db->lastErrorMsg());
    }

    $stmt->bindValue(":username", $teacher_username, SQLITE3_TEXT);
    $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);

    $result = $stmt->execute();
    if (!$result) {
        errorMessages("Error executing update query", $db->lastErrorMsg());
    }

    # Handle password reset if requested
    if (isset($_POST["reset_password"]) && $_POST["reset_password"] == "1") {
        if ($teacher["teacher_username"] == "ADMIN") {
            $unsafe_password = genPassword(8);
        } else {
            $unsafe_password = genPassword(6);
        }
        $password = password_hash($unsafe_password, PASSWORD_DEFAULT);

        $query =
            "UPDATE TEACHERS SET teacher_password = :password WHERE teacher_id = :id";
        $stmt = $db->prepare($query);

        if (!$stmt) {
            errorMessages(
                "Error preparing password update query",
                $db->lastErrorMsg(),
            );
        }

        $stmt->bindValue(":password", $password, SQLITE3_TEXT);
        $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);

        $result = $stmt->execute();
        if (!$result) {
            errorMessages(
                "Error executing password update query",
                $db->lastErrorMsg(),
            );
        }

        # Create credentials file content
        $credentials_content = "NextStep Teacher Account - Password Reset\n";
        $credentials_content .= "=========================================\n\n";
        $credentials_content .= "Username: " . $teacher_username . "\n";
        $credentials_content .= "New Password: " . $unsafe_password . "\n\n";
        $credentials_content .= "Reset: " . date("Y-m-d H:i:s") . "\n\n";

        $_SESSION["new_teacher_credentials"] = $credentials_content;
        $_SESSION["new_teacher_filename"] =
            $teacher_username . "_password_reset.txt";

        $db->close();
        header("Location: /NextStep/download/download_success.php");
        exit();
    }

    $_SESSION["success"] = "Teacher $teacher_username updated successfully";
    $db->close();
    //header("Location: /NextStep/teachers/");
    header("Location: /NextStep/teachers/edit_teacher.php?teacher_id=" . $teacher_id);

    exit();
}

$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);

$query = "SELECT teacher_username
          FROM TEACHERS WHERE teacher_id = :id";
$stmt = $db->prepare($query);

if (!$stmt) {
    errorMessages("Error preparing query", $db->lastErrorMsg());
}

$stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
$result = $stmt->execute();

if (!$result) {
    errorMessages("Error executing query", $db->lastErrorMsg());
}

$teacher = $result->fetchArray();

if (!$teacher) {
    $_SESSION["error"] = "Teacher not found";
    header("Location: /NextStep/teachers/");
    $db->close();
    exit();
}

$is_admin = $teacher["teacher_username"] == "ADMIN";

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="../css/style_navbar.css"/>
<link rel="stylesheet" href="../css/style_page.css"/>
<title>NextStep - Edit Teacher</title>
</head>
<body class="theme-<?= $color_theme ?>">
<?php include "../navbar.php"; ?>
<div class="page-box-wide">
<h2>Edit Teacher</h2>
<?php flashMessages(); ?>

<form method="POST" action="edit_teacher.php?teacher_id=<?= $teacher_id ?>">
    <label for="teacher_username">Username:</label>
    <input type="text" id="teacher_username" name="teacher_username"
           value="<?= htmlspecialchars($teacher["teacher_username"]) ?>"
           <?= $is_admin ? "readonly" : "" ?>/>

    <div class="password-reset-container">
        <input type="checkbox" name="reset_password" value="1" class="checkbox-input"/>
        <label class="checkbox-label">Reset password and download new credentials</label>
        <p class="help-text">
            Check this box to generate a new password for this teacher
        </p>
    </div>

    <div class="button-container">
        <input type="submit" class="simple-btn" name="submit" value="Update Teacher">
        <a href="/NextStep/teachers/" class="simple-btn cancel-btn">Cancel</a>
    </div>
</form>
</div>
<script src="js/script.js"></script>
</body>
</html>
