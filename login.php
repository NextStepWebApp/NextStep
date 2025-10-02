<?php
require_once "utils.php";

session_start();

$db_file = "nextstep_data.db";
$db = new SQLite3($db_file);
// Check database connection
if (!$db) {
    $_SESSION["error"] = "Database connection failed";
    header("Location: login.php");
    exit();
}

if (isset($_POST["username"]) && isset($_POST["password"])) {
    session_unset();
    if (strlen($_POST["username"]) < 1 || strlen($_POST["password"]) < 1) {
        $_SESSION["error"] = "Email and password are required";
        header("Location: login.php");
        exit();
    } else {
        $query =
            "SELECT teacher_email, teacher_name, teacher_username, teacher_password FROM TEACHERS WHERE teacher_username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindValue(":username", $_POST["username"], SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray();

        if ($row) {
            if (hash_equals($_POST["password"], $row["teacher_password"])) {
                #session_regenerate_id(true); // Prevent session fixation
                $_SESSION["teacher_email"] = $row["teacher_email"];
                $_SESSION["teacher_name"] = $row["teacher_name"];
                $_SESSION["teacher_username"] = $row["teacher_username"];
                header("Location: index.php");
                exit();
            } else {
                $_SESSION["error"] = "Invalid password";
                $_SESSION["old_username"] = $_POST["username"];
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION["error"] = "No user found with that username";
            header("Location: login.php");
            exit();
        }
    }
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="icon" type="image/x-icon" href="images/logo.webp" />
        <link rel="stylesheet" href="css/style_login.css" />
        <script src="js/script.js"></script>
        <title>NextStep</title>
    </head>
    <body>
        <div class="login-container">
            <div class="brand-name">NextStep</div>
            <div class="welcome">Welcome back</div>

            <?php
            flashMessages();
            $old_username = "";
            if (isset($_SESSION["old_username"])) {
                $old_username = $_SESSION["old_username"];
                unset($_SESSION["old_username"]);
            }
            ?>

            <form method="POST">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username" value="<?php echo htmlentities(
                        $old_username,
                    ); ?>" />
                </div>

                <div class="input-group">
                    <input
                        type="password"
                        name="password"
                        placeholder="Password"
                        id="password"
                    />
                    <span class="toggle-password" onclick="togglePassword()"
                        >Show</span
                    >
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>
        </div>
    </body>
</html>
