<?php
require_once "utils.php";
session_start();
loginSecurity();

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

$query = "SELECT teacher_id FROM TEACHERS WHERE teacher_name = :name";
$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error getting teacher id", $db->lastErrorMsg());
}
$stmt->bindValue(":name", $_SESSION["teacher_username"], SQLITE3_TEXT);
$result = $stmt->execute();
if (!$result) {
    errorMessages("Error executing update query", $db->lastErrorMsg());
}
$result = $result->fetchArray();
$teacher_id = $result["teacher_id"];

# Handle form submission
if (isset($_POST["submit"])) {
    # Check if all fields are filled
    if (empty($_POST["account_name"]) || empty($_POST["account_email"])) {
        $_SESSION['error'] = "All fields are required";
        header("Location: All fields are required");
        $db->close();
        exit();
    } else {
        $query = "SELECT teacher_id FROM TEACHERS WHERE (teacher_name = :name OR teacher_email = :email) AND teacher_id != :id";
        $stmt = $db->prepare($query);
    
        if (!$stmt) {
            errorMessages("Error preparing check query", $db->lastErrorMsg());
        }
        $stmt->bindValue(":name", $_POST["account_name"], SQLITE3_TEXT);
        $stmt->bindValue(":email", $_POST["account_email"], SQLITE3_TEXT);
        $stmt->bindValue(":id", $teacher_id, SQLITE3_TEXT);
        $result = $stmt->execute();
        if (!$result) {
            errorMessages("Error executing check query", $db->lastErrorMsg());
        }
        $existing = $result->fetchArray();
        if ($existing) {
            $_SESSION['error'] = "A teacher with this name or email already exists";
            header("Location: settings.php");
            $db->close();
            exit();
        }
        
        # Update
        $query = "UPDATE TEACHERS SET teacher_name = :name WHERE teacher_id = :id";
        
        
        $stmt = $db->prepare($query);
        if (!$stmt) {
            errorMessages("Error preparing update query", $db->lastErrorMsg());
        }
        
        $stmt->bindValue(":name", $teacher_name, SQLITE3_TEXT);
        $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
        
        $result = $stmt->execute();
        if (!$result) {
            errorMessages("Error executing update query", $db->lastErrorMsg());
        }
        $db->close();
        header("Location: download_success.php");
        exit();
        
    }
    $db->close();
}



# This is the normal page loading 

$query = "SELECT teacher_name, teacher_email FROM TEACHERS WHERE teacher_username = :username";
$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing update query", $db->lastErrorMsg());
}
$stmt->bindValue(":username", $_SESSION["teacher_username"], SQLITE3_TEXT);
$result = $stmt->execute();
if (!$result) {
    errorMessages("Error executing update query", $db->lastErrorMsg());
}
$information = $result->fetchArray();
$teacher_name = htmlspecialchars($information["teacher_name"]);
$teacher_email = htmlspecialchars($information["teacher_email"]);
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" type="image/x-icon" href="images/logo.webp"/>
    <link rel="stylesheet" href="css/style_navbar.css"/>
    <link rel="stylesheet" href="css/style_page.css"/>
    <title>NextStep - Settings</title>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <!-- Settings Container -->
    <div class="settings-container">
        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" data-tab="general">General</button>
            <button class="tab" data-tab="users">Users</button>
            <button class="tab" data-tab="data">Data</button>
            <button class="tab" data-tab="preferences">Preferences</button>
            <button class="tab" data-tab="advanced">Advanced</button>
        </div>

        <!-- Tab Content: General -->
        <div class="tab-content active" id="general">
            <h2>General Settings</h2>
                <form method="POST" action="settings.php">
                    <h3 style="margin-top: 30px; margin-bottom: 15px;">Account Information</h3>
                        <div class="form-group">
                            <label for="account_name">Name</label>
                            <input type="text" id="account_name" name="account_name" value="<?= $teacher_name ?>" />
                        </div>
            
                        <div class="form-group">
                            <label for="account_email">Email</label>
                            <input type="email" id="account_email" name="account_email" value="<?= $teacher_email ?>" />
                        </div>
                                   <h3 style="margin-top: 30px; margin-bottom: 15px;">Display Preferences</h3>
            
                        <div class="form-group">
                            <label for="theme">Theme</label>
                            <select id="theme" name="theme">
                                <option value="light" selected>Light</option>
                                <option value="dark">Dark</option>
                            </select>
                        </div>
            
                        <div class="button-group">
                            <button type="submit" class="simple-btn primary" name="submit_general">Save Changes</button>
                            <button type="button" class="simple-btn">Cancel</button>
                        </div>
                </form>
        </div>

        <!-- Tab Content: Users -->
        <div class="tab-content" id="users">
            <h2>User Management</h2>
            <p>Manage users and permissions.</p>
            
            <!-- Add your content here -->
        </div>

        <!-- Tab Content: Data -->
        <div class="tab-content" id="data">
            <h2>Data Settings</h2>
            <p>Configure data-related settings.</p>
            
            <!-- Add your content here -->
        </div>

        <!-- Tab Content: Preferences -->
        <div class="tab-content" id="preferences">
            <h2>Preferences</h2>
            <p>Customize your preferences.</p>
            
            <!-- Add your content here -->
        </div>

        <!-- Tab Content: Advanced -->
        <div class="tab-content" id="advanced">
            <h2>Advanced Settings</h2>
            <p>Advanced configuration options.</p>
            
            <!-- Add your content here -->
        </div>
    </div>
<script src="js/script.js"></script>
</body>
</html>
