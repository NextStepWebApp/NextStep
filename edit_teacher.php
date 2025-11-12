<?php
require_once "utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);
check_id($_GET["teacher_id"]);

$teacher_id = $_GET['teacher_id'];

$db = new SQLite3($db_file);

# Fetch teacher data
$query = "SELECT teacher_id, teacher_name, teacher_username, teacher_email 
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
    $_SESSION['error'] = "Teacher not found";
    header("Location: teachers.php");
    $db->close();
    exit();
}

# Prevent editing ADMIN account username
$is_admin = ($teacher['teacher_username'] == 'ADMIN');

# Handle form submission
if (isset($_POST["submit"])) {

    # Check if all fields are filled
    if (empty($_POST["teacher_name"]) || empty($_POST["teacher_email"]) || 
       empty($_POST["teacher_username"])) {

        $_SESSION['error'] = "All fields are required";
        header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
        exit();
    }
    
    # Validate teacher name
    $teacher_name = trim($_POST["teacher_name"]);
    if (strlen($teacher_name) < 2) {
        $_SESSION['error'] = "Name must be at least 2 characters long";
        header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
        exit();
    }
    
    if (strlen($teacher_name) > 50) {
        $_SESSION['error'] = "Name must not exceed 50 characters";
        header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
        exit();
    }
    
    if (!preg_match("/^[a-zA-Z\s\-'\.]+$/u", $teacher_name)) {
        $_SESSION['error'] = "Name contains invalid characters";
        header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
        exit();
    }
    
    # Validate email
    $teacher_email = trim($_POST["teacher_email"]);
    if (!filter_var($teacher_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
        exit();
    }
    
    if (strlen($teacher_email) > 50) {
        $_SESSION['error'] = "Email must not exceed 50 characters";
        header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
        exit();
    }
    
    # Validate username
    $teacher_username = trim($_POST["teacher_username"]);
    
    # Prevent changing ADMIN username
    if ($is_admin && $teacher_username != 'ADMIN') {
        $_SESSION['error'] = "Cannot change ADMIN username";
        header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
        exit();
    }
    
    if (strlen($teacher_username) < 3) {
        $_SESSION['error'] = "Username must be at least 3 characters long";
        header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
        exit();
    }
    
    if (strlen($teacher_username) > 30) {
        $_SESSION['error'] = "Username must not exceed 30 characters";
        header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
        exit();
    }
    
    if (!preg_match("/^[a-zA-Z0-9_\-]+$/", $teacher_username)) {
        $_SESSION['error'] = "Username can only contain letters, numbers, hyphens and underscores";
        header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
        exit();
    }
   
    # Check if email or username already exists not looking at the current teacher 
    $query = "SELECT teacher_id FROM TEACHERS WHERE 
              (teacher_email = :email OR teacher_username = :username) 
              AND teacher_id != :id";
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        errorMessages("Error preparing check query", $db->lastErrorMsg());
    }
    
    $stmt->bindValue(":email", $teacher_email, SQLITE3_TEXT);
    $stmt->bindValue(":username", $teacher_username, SQLITE3_TEXT);
    $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    if (!$result) {
        errorMessages("Error executing check query", $db->lastErrorMsg());
    }
       
    $existing = $result->fetchArray();
    
    if ($existing) {
        $_SESSION['error'] = "A teacher with this email or username already exists";
        header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
        $db->close();
        exit();
    }
    
    # Update teacher
    $query = "UPDATE TEACHERS SET 
              teacher_name = :name,
              teacher_email = :email,
              teacher_username = :username
              WHERE teacher_id = :id";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing update query", $db->lastErrorMsg());
    }

    $stmt->bindValue(":name", $teacher_name, SQLITE3_TEXT);
    $stmt->bindValue(":email", $teacher_email, SQLITE3_TEXT);
    $stmt->bindValue(":username", $teacher_username, SQLITE3_TEXT);
    $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);

    $result = $stmt->execute();
    if (!$result) {
        errorMessages("Error executing update query", $db->lastErrorMsg());
    }
    
    # Handle password reset if requested
    if (isset($_POST["reset_password"]) && $_POST["reset_password"] == "1") {
        $unsafe_password = genPassword(6); 
        $password = password_hash($unsafe_password, PASSWORD_DEFAULT);
        
        $query = "UPDATE TEACHERS SET teacher_password = :password WHERE teacher_id = :id";
        $stmt = $db->prepare($query);
        
        if (!$stmt) {
            errorMessages("Error preparing password update query", $db->lastErrorMsg());
        }
        
        $stmt->bindValue(":password", $password, SQLITE3_TEXT);
        $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
        
        $result = $stmt->execute();
        if (!$result) {
            errorMessages("Error executing password update query", $db->lastErrorMsg());
        }
        
        # Create credentials file content
        $credentials_content = "NextStep Teacher Account - Password Reset\n";
        $credentials_content .= "=========================================\n\n";
        $credentials_content .= "Name: " . $teacher_name . "\n";
        $credentials_content .= "Email: " . $teacher_email . "\n";
        $credentials_content .= "Username: " . $teacher_username . "\n";
        $credentials_content .= "New Password: " . $unsafe_password . "\n\n";
        $credentials_content .= "Reset: " . date('Y-m-d H:i:s') . "\n\n";
        
        $_SESSION["new_teacher_credentials"] = $credentials_content;
        $_SESSION["new_teacher_filename"]    = $teacher_username . "_password_reset.txt";
        
        $db->close();
        header("Location: download_success.php");
        exit();
    }
    
    $_SESSION['success'] = "Teacher updated successfully";
    $db->close();
    header("Location: teachers.php");
    exit();
}

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
<title>NextStep - Edit Teacher</title>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="page-box-wide">
<h2>Edit Teacher</h2>
<?php flashMessages(); ?>

<form method="POST" action="edit_teacher.php?teacher_id=<?= $teacher_id ?>">
    <label for="teacher_name">Name:</label>
    <input type="text" name="teacher_name" 
           value="<?= htmlspecialchars($teacher['teacher_name']) ?>"/>
    <label for="teacher_email">Email:</label>
    <input type="email" name="teacher_email" 
           value="<?= htmlspecialchars($teacher['teacher_email']) ?>"/>
    <label for="teacher_username">Username:</label>
    <input type="text" id="teacher_username" name="teacher_username" 
           value="<?= htmlspecialchars($teacher['teacher_username']) ?>"
           <?= $is_admin ? 'readonly' : '' ?>/>
    
    <div style="margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 5px;">
        <label style="display: flex; align-items: center; cursor: pointer;">
            <input type="checkbox" name="reset_password" value="1" 
                   style="margin-right: 10px; width: auto;"/>
            <span>Reset password and download new credentials</span>
        </label>
        <p style="color: #666; font-size: 0.9em; margin: 5px 0 0 0;">
            Check this box to generate a new password for this teacher
        </p>
    </div>
    <div class="button-container">
        <input type="submit" class="nav-btn" name="submit" value="Update Teacher">
        <a href="teachers.php" class="nav-btn cancel-btn">Cancel</a>
    </div>
</form>
</div>
<script src="js/script.js"></script>
</body>
</html>
