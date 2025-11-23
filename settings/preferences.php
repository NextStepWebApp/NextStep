<?php
# This is called in the utils
#$branding = json_decode(file_get_contents($branding_path), true);
$current_app_name = $branding['app_name'] ?? 'NextStep';

# Handle form submission for branding
if (isset($_POST["branding_action"])) {
    #$branding = json_decode(file_get_contents($branding_path), true);
    
    if (empty($_POST["app_name"])) {
        $_SESSION['error'] = "Application name cannot be empty";
    } else {
        $app_name = trim($_POST["app_name"]);
        
        # Validate app name
        if (strlen($app_name) < 2) {
            $_SESSION['error'] = "Application name must be at least 2 characters long";
        } elseif (strlen($app_name) > 30) {
            $_SESSION['error'] = "Application name must not exceed 30 characters";
        } elseif (!preg_match("/^[a-zA-Z0-9\s\-_]+$/", $app_name)) {
            $_SESSION['error'] = "Application name can only contain letters, numbers, spaces, hyphens and underscores";
        } else {
            # Update branding
            $branding['app_name'] = $app_name;
            
            if (file_put_contents($branding_path, json_encode($branding, JSON_PRETTY_PRINT)) === false) {
                $_SESSION['error'] = "Failed to save branding configuration";
            } else {
                $_SESSION['success'] = "Application name updated successfully";
            }
        }
    }
    
    header("Location: /NextStep/settings/?tab=preferences");
    exit();
}

?>
<h2>Preferences</h2>
<?php flashMessages(); ?>

<form method="POST" action="/NextStep/settings/?tab=preferences">
    <h3 class="extra-spacing">Application Name</h3>
    <input type="text" name="app_name" value="<?= htmlspecialchars($current_app_name) ?>"/>
    <div class="button-container">
        <input type="submit" class="simple-btn" name="branding_action" value="Save Changes">
        <a href="/NextStep/settings?tab=general" class="simple-btn cancel-btn">Cancel</a>
    </div>
</form>


<h3 class="extra-spacing">Display Preferences</h3>

<select name="theme">
    <option value="light" selected>Light</option>
    <option value="dark">Dark</option>
</select>
