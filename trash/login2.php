<?php
session_start();
require_once "utils.php";

$db_file = "nextstep_data.db";
$db = new SQLite3($db_file);

// Check database connection
if (!$db) {
    $_SESSION["error"] = "Database connection failed";
    header("Location: login.php");
    exit();
}

// Process login form submission
if (isset($_POST["username"]) && isset($_POST["password"])) {
    session_unset();

    // Basic input validation
    if (strlen($_POST["username"]) < 1 || strlen($_POST["password"]) < 1) {
        $_SESSION["error"] = "Username and password are required";
        $_SESSION['form_data'] = [
            'username' => $_POST['username'],
        ];
        header("Location: login.php");
        exit();
    } else {
        // Get user data from database
        $query = "SELECT teacher_email, teacher_name, teacher_username, teacher_password FROM TEACHERS WHERE teacher_username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindValue(":username", $_POST["username"], SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($row) {
            // Verify hashed password (assumes passwords are hashed in the database)
            if (password_verify($_POST["password"], $row["teacher_password"])) {
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION["teacher_email"] = $row["teacher_email"];
                $_SESSION["teacher_name"] = $row["teacher_name"];
                $_SESSION["teacher_username"] = $row["teacher_username"];
                $_SESSION["success"] = "Log in successful.";
                error_log("Login success " . $_POST["username"]);
                header("Location: index.php");
                exit();
            } else {
                error_log("Login fail " . $_POST["username"]);
                $_SESSION['form_data'] = [
                    'username' => $_POST['username'],
                ];
                $_SESSION["error"] = "Invalid login credentials";
                header("Location: login.php");
                exit();
            }
        } else {
            error_log("Login fail " . $_POST["username"] . " - User not found");
            $_SESSION["error"] = "Invalid login credentials";
            header("Location: login.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="NextStep Team">
    <meta name="description" content="NextStep Teacher Login Portal">
    <meta name="keywords" content="NextStep, Teacher, Login, Education">
    <meta name="robots" content="index, follow">
    <link rel="icon" type="image/x-icon" href="images/logo.webp">
    <title>NextStep - Login</title>
    <link rel="stylesheet" href="css/style_login.css">
    <script src="js/script.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="brand-name">NextStep</div>
        <div class="welcome">Welcome back</div>
        <?php
        flashMessages();
        $username = '';
        $name = '';
        getFormData($username, $name);
        ?>
        <form method="POST" autocomplete="off">
            <div class="input-group">
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo htmlentities($username); ?>"
                    placeholder="Username"
                    required
                    autocomplete="off"
                    aria-autocomplete="none"
                />
            </div>
            <div class="input-group">
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Password"
                    required
                    autocomplete="new-password"
                    aria-autocomplete="none"
                />
                <span class="toggle-password" onclick="togglePassword()">Show</span>
            </div>
            <button type="submit" onclick="return doValidate();" class="login-btn">Login</button>
        </form>
    </div>
</body>
</html>
