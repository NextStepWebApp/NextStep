<?php
$app_name = $branding["app_name"]; # from utils
# Connect to the db
try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

$theme = color_theme_helper($db, $color_theme_system["theme_color"]);

$allowed_themes = ["blue", "red", "green", "purple"];

if (isset($_POST["preferences"])) {
    # App name
    if (empty($_POST["app_name"])) {
        $_SESSION["error"] = "Application name cannot be empty";
    } else {
        $app_name = trim($_POST["app_name"]);

        if (strlen($app_name) < 2) {
            $_SESSION["error"] =
                "Application name must be at least 2 characters long";
        } elseif (strlen($app_name) > 30) {
            $_SESSION["error"] =
                "Application name must not exceed 30 characters";
        } elseif (!preg_match("/^[a-zA-Z0-9\s\-_]+$/", $app_name)) {
            $_SESSION["error"] =
                "Application name can only contain letters, numbers, spaces, hyphens and underscores";
        }
    }

    # Color theme
    if (isset($_POST["theme"])) {
        if (!in_array($_POST["theme"], $allowed_themes, true)) {
            $_SESSION["error"] = "Invalid theme selected";
        } else {
            $theme = $_POST["theme"];
        }
    }

    if (!isset($_SESSION["error"])) {
        # Branding
        $branding["app_name"] = $app_name;

        if (
            file_put_contents(
                $branding_path,
                json_encode($branding, JSON_PRETTY_PRINT),
            ) === false
        ) {
            $_SESSION["error"] = "Failed to save branding settings.";
            $db->close();
            header("Location: /NextStep/settings/?tab=preferences");
            exit();
        }

        # Theme

        # Get the foreign key for the theme color
        function get_key_theme(SQLite3 $db, string $theme)
        {
            $query =
                "SELECT theme_id FROM THEME WHERE theme_name = :theme_name";
            $stmt = $db->prepare($query);
            $stmt->bindValue(":theme_name", $theme, SQLITE3_TEXT);
            $result = $stmt->execute();
            $row = $result->fetchArray();
            try {
                $theme_id = $row[0];
                return $theme_id;
            } catch (Exception) {
                $theme_id = null;
                return $theme_id;
            }
        }
        $theme_id = get_key_theme($db, $theme);
        if ($theme_id === null) {
            $query = "INSERT INTO THEME (theme_name) VALUES (:theme_name)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(":theme_name", $theme, SQLITE3_TEXT);
            $result = $stmt->execute();

            # now get it
            $theme_id = get_key_theme($db, $theme);
        }

        # preperation to see if whe have a admin
        # Seeing what method to use

        $method = is_admin_checker($db);
        if ($method == "sql") {
            $query =
                "UPDATE TEACHERS SET teacher_theme_id = :theme_id WHERE teacher_id = :teacher_id";
            $stmt = $db->prepare($query);
            $stmt->bindValue(":theme_id", $theme_id, SQLITE3_INTEGER);
            $stmt->bindValue(
                ":teacher_id",
                $_SESSION["teacher_id"],
                SQLITE3_INTEGER,
            );
            $result = $stmt->execute();
        } elseif ($method == "json") {
            $color_theme_system["theme_color"] = $theme;

            if (
                file_put_contents(
                    $color_theme_path,
                    json_encode($color_theme_system, JSON_PRETTY_PRINT),
                ) === false
            ) {
                $_SESSION["error"] = "Failed to save theme settings.";
                $db->close();
                header("Location: /NextStep/settings/?tab=preferences");
                exit();
            }
        }

        $_SESSION["success"] = "Preferences updated successfully";
    }

    header("Location: /NextStep/settings/?tab=preferences");
    exit();
}

$db->close();
?>
<?php flashMessages(); ?>
<h2>Preferences</h2>
<form method="POST" action="/NextStep/settings/?tab=preferences">

    <h3 class="extra-spacing">Application Name</h3>
    <input type="text" name="app_name"
           value="<?= htmlspecialchars($app_name) ?>">

    <h3 class="extra-spacing">Color Theme</h3>
    <select name="theme">
        <?php foreach ($allowed_themes as $color) {
            echo "<option value='" .
                htmlspecialchars($color) .
                "'" .
                ($color === $theme ? " selected" : "") .
                ">" .
                htmlspecialchars($color) .
                "</option>";
        } ?>
    </select>

    <div class="button-container">
        <input type="submit" class="simple-btn" name="preferences" value="Save Changes">
        <a href="/NextStep/settings?tab=preferences"
           class="simple-btn cancel-btn">Cancel</a>
    </div>
</form>
