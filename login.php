<?php
session_start();
require_once "utils.php";
setup_checker();

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
    header("Location: login.php");
    exit();
}

if (isset($_POST["username"]) && isset($_POST["password"])) {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username === "" || $password === "") {
        $_SESSION["error"] = "Username and password are required";
        header("Location: login.php");
        exit();
    }

    # Get all the info you need from the user
    $query = "SELECT teacher_id, teacher_username, teacher_password, role_name
              FROM TEACHERS JOIN ROLES ON TEACHERS.teacher_role_id = ROLES.role_id
              WHERE teacher_username = :username";

    $stmt = $db->prepare($query);
    $stmt->bindValue(":username", $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray();

    if ($row) {
        $hash = $row["teacher_password"];

        if (password_verify($password, $hash)) {
            # Rehash if needed
            if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $update = $db->prepare(
                    "UPDATE TEACHERS SET teacher_password = :password WHERE teacher_username = :username",
                );
                $update->bindValue(":password", $newHash, SQLITE3_TEXT);
                $update->bindValue(":username", $username, SQLITE3_TEXT);
                $update->execute();
            }

            session_regenerate_id(true);
            $_SESSION["teacher_id"] = $row["teacher_id"];
            $_SESSION["teacher_username"] = $row["teacher_username"];
            $_SESSION["teacher_role"] = $row["role_name"];

            $db->close();
            header("Location: index.php");
            exit();
        } else {
            # Wrong password
            $_SESSION["error"] = "Invalid password";
            $_SESSION["old_username"] = $username;
            $db->close();
            header("Location: login.php");
            exit();
        }
    } else {
        # No user found
        $_SESSION["error"] = "No user found with that username";
        $_SESSION["old_username"] = $username;
        $db->close();
        header("Location: login.php");
        exit();
    }
}

$db->close();
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="icon" type="image/x-icon" href="images/logo.webp" />
        <link rel="stylesheet" href="css/style_login.css" />
        <script src="js/script.js"></script>
        <title>NextStep - Login</title>
    </head>
    <body class="theme-<?= $color_theme_system["theme_color"] ?>">
        <div class="login-container">
            <div class="brand-name"><?= $branding["app_name"] ?></div>
            <div class="welcome">Welcome back</div>

            <?php
            flashMessages();

            $old_username = $_SESSION["old_username"] ?? "";
            unset($_SESSION["old_username"]);
            ?>

            <form method="POST">
                <div class="input-group">
                    <input
                        type="text"
                        name="username"
                        placeholder="Username"
                        value="<?php echo htmlentities($old_username); ?>"
                    />
                </div>

                <div class="input-group">
                    <input
                        type="password"
                        name="password"
                        placeholder="Password"
                        id="password"
                    />
                    <span class="toggle-password" onclick="togglePassword()">Show</span>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>
        </div>
    </body>
</html>
