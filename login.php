<?php
require_once "utils.php";
session_start();

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

if (isset($_POST["username"]) && isset($_POST["password"])) {
    session_unset();
    if (strlen($_POST["username"]) < 1 || strlen($_POST["password"]) < 1) {
        $_SESSION["error"] = "Username and password are required"; 
        header("Location: login.php");
        exit();
    } else {
        $query =
            "SELECT teacher_password FROM TEACHERS WHERE teacher_username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindValue(":username", $_POST["username"], SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray();

        if ($row) {
            $password = $_POST["password"];
            $hash = $row["teacher_password"];
            if (password_verify($password, $hash)) {
                # Check if either the algorithm or the options have changed
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    # If so, create a new hash, and replace the old one
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    # update the passord in database
                    $query = "UPDATE TEACHERS SET teacher_password = :password WHERE teacher_email = :email";
                    $stmt = $db->prepare($query);
                    if (!$stmt) {
                        errorMessages("Error preparing query in login", $db->lastErrorMsg());
                    }
                    $stmt->bindValue(":password", $newHash, SQLITE3_TEXT);
                    $stmt->bindValue(":email", $row["teacher_email"], SQLITE3_TEXT);
                    
                    $results = $stmt->execute();
                    if (!$results) {
                        errorMessages("Error executing query in login", $db->lastErrorMsg());
                    }
                }    
                #session_regenerate_id(true); // Prevent session fixation
                $_SESSION["teacher_username"] = $row["teacher_username"];
                $db->close();
                header("Location: index.php");
                exit();
            } else {
                $_SESSION["error"] = "Invalid password";
                $_SESSION["old_username"] = $_POST["username"];
                $db->close();
                header("Location: login.php");
                exit();
            }
        } else {
            errorMessages("No user found with that username", $db->lastErrorMsg());
            header("Location: login.php");
            $db->close();
            exit();
        }
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
