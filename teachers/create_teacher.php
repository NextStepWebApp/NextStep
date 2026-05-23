<?php
require_once "../utils.php";
session_start();
loginSecurity();
require_permission("teachers_access");

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);

if (isset($_POST["submit"])) {
    # Check if all fields are filled
    if (empty($_POST["teacher_name"]) || empty($_POST["teacher_username"])) {
        $_SESSION["error"] = "All fields are required";
        header("Location: create_teacher.php");
        $db->close();
        exit();
    }

    # This section will be validations of name, email, username and password
    # These are functions in utils.php that are used for user input validations
    # Validate teacher name
    $teacher_name = trim($_POST["teacher_name"]);
    validate_teacher_name($db, $teacher_name, "create_teacher", $teacher_name);

    # Validate user name (same function)
    $teacher_username = trim($_POST["teacher_username"]);
    validate_teacher_name(
        $db,
        $teacher_username,
        "create_teacher",
        $teacher_username,
    );

    # Check to see if the teacher already exists
    $query = "SELECT teacher_id FROM TEACHERS WHERE
          teacher_username = :username OR teacher_username = :name";
    $stmt = $db->prepare($query);

    if (!$stmt) {
        errorMessages("Error preparing check query", $db->lastErrorMsg());
    }

    $stmt->bindValue(":username", $teacher_username, SQLITE3_TEXT);
    $stmt->bindValue(":name", $teacher_name, SQLITE3_TEXT);
    $result = $stmt->execute();

    if (!$result) {
        errorMessages("Error executing check query", $db->lastErrorMsg());
    }

    $existing = $result->fetchArray();

    if ($existing) {
        $_SESSION["error"] =
            "A teacher with this username or name already exists";
        header("Location: create_teacher.php");
        $db->close();
        exit();
    }
    $unsafe_password = genPassword(6);
    # Hash the password
    $password = password_hash($unsafe_password, PASSWORD_DEFAULT);

    # This is a function in utils.php
    # This gets the role foreign key for teachers insert
    $role_key = get_foreign_key_roles($db, "USER");

    # Insert new teacher
    $query = "
        INSERT INTO TEACHERS (
            teacher_name,
            teacher_email,
            teacher_username,
            teacher_password,
            teacher_role_id
        ) VALUES (
            :name,
            :email,
            :username,
            :password,
            :role
        )
    ";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing insert query", $db->lastErrorMsg());
    }

    $stmt->bindValue(":name", $teacher_name, SQLITE3_TEXT);
    $stmt->bindValue(":email", "example@example.com", SQLITE3_TEXT);
    $stmt->bindValue(":username", $teacher_username, SQLITE3_TEXT);
    $stmt->bindValue(":password", $password, SQLITE3_TEXT);
    $stmt->bindValue(":role", $role_key, SQLITE3_INTEGER);

    $result = $stmt->execute();
    if (!$result) {
        errorMessages("Error executing insert query", $db->lastErrorMsg());
    }

    # Create credentials file content
    $credentials_content = "NextStep Teacher Account Credentials\n";
    $credentials_content .= "=====================================\n\n";
    $credentials_content .= "Name: " . $teacher_name . "\n";
    $credentials_content .= "Username: " . $teacher_username . "\n";
    $credentials_content .= "Password: " . $unsafe_password . "\n\n";
    $credentials_content .= "Created: " . date("Y-m-d H:i:s") . "\n\n";

    $_SESSION["new_teacher_credentials"] = $credentials_content;
    $_SESSION["new_teacher_filename"] = $teacher_username . "_credentials.txt";

    $db->close();
    header("Location: /NextStep/download/download_success.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="../css/style_navbar.css"/>
<link rel="stylesheet" href="../css/style_page.css"/>
<title>NextStep - Create Teacher</title>
</head>
<body class="theme-<?= $color_theme ?>">
<?php include "../navbar.php"; ?>
<div class="page-box-wide">
<h2>Create Teacher</h2>
<?php flashMessages(); ?>

<form method="POST" action="create_teacher.php">
    <label for="teacher_name">Name:</label>
    <input type="text" id="teacher_name" name="teacher_name"/>
    <label for="teacher_username">Username:</label>
    <input type="text" id="teacher_username" name="teacher_username"/>
    <div class="button-container">
        <input type="submit" class="simple-btn" name="submit" value="Create Teacher">
        <a href="/NextStep/teachers/" class="simple-btn cancel-btn">Cancel</a>
    </div>
</form>
</div>
<script src="../js/script.js"></script>
</body>
</html>
