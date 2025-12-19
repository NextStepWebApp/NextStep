<?php
$current_app_name = $branding["app_name"];
$selected_theme = $color_theme["theme_color"];

if (isset($_POST["preferences"])) {
    $app_name = $current_app_name;
    $theme = $selected_theme;

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
        $allowed_themes = ["blue", "red", "green", "purple"];

        if (!in_array($_POST["theme"], $allowed_themes, true)) {
            $_SESSION["error"] = "Invalid theme selected";
        } else {
            $theme = $_POST["theme"];
        }
    }

    # Save
    if (!isset($_SESSION["error"])) {
        $branding["app_name"] = $app_name;
        $color_theme["theme_color"] = $theme;

        file_put_contents(
            $branding_path,
            json_encode($branding, JSON_PRETTY_PRINT),
        );
        file_put_contents(
            $color_theme_path,
            json_encode($color_theme, JSON_PRETTY_PRINT),
        );

        $_SESSION["success"] = "Preferences updated successfully";
    }

    header("Location: /NextStep/settings/?tab=preferences");
    exit();
}
?>
<?php flashMessages(); ?>
<h2>Preferences</h2>
<form method="POST" action="/NextStep/settings/?tab=preferences">

    <h3 class="extra-spacing">Application Name</h3>
    <input type="text" name="app_name"
           value="<?= htmlspecialchars($current_app_name) ?>">

    <h3 class="extra-spacing">Color Theme</h3>
    <select name="theme">
        <?php foreach (["blue", "red", "green", "purple"] as $color) {
            echo "<option value='$color'" .
                ($color === $selected_theme ? " selected" : "") .
                ">$color</option>";
        } ?>
    </select>

    <div class="button-container">
        <input type="submit" class="simple-btn" name="preferences" value="Save Changes">
        <a href="/NextStep/settings?tab=general"
           class="simple-btn cancel-btn">Cancel</a>
    </div>
</form>
