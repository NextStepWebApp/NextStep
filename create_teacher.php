<?php
require_once "utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);


if (isset($_POST["submit"])) {

    # Check if all fields are filled
    if (empty($_POST["teacher_name"]) || empty($_POST["teacher_email"]) || 
       empty($_POST["teacher_username"])) {

        $_SESSION['error'] = "All fields are required";
        header("Location: create_teacher.php");
        exit();
    }
    
    # This section will be validations of name, email, username and password
    
    # Validate teacher name
    $teacher_name = trim($_POST["teacher_name"]);
    if (strlen($teacher_name) < 2) {
        $_SESSION['error'] = "Name must be at least 2 characters long";
        header("Location: create_teacher.php");
        exit();
    }
    
    if (strlen($teacher_name) > 50) {
        $_SESSION['error'] = "Name must not exceed 50 characters";
        header("Location: create_teacher.php");
        exit();
    }
    
    if (!preg_match("/^[a-zA-Z\s\-'\.]+$/u", $teacher_name)) {
        $_SESSION['error'] = "Name contains invalid characters";
        header("Location: create_teacher.php");
        exit();
    }
    
    # Validate email
    $teacher_email = trim($_POST["teacher_email"]);
    if (!filter_var($teacher_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: create_teacher.php");
        exit();
    }
    
    if (strlen($teacher_email) > 50) {
        $_SESSION['error'] = "Email must not exceed 50 characters";
        header("Location: create_teacher.php");
        exit();
    }
    
    # Validate username
    $teacher_username = trim($_POST["teacher_username"]);
    if (strlen($teacher_username) < 3) {
        $_SESSION['error'] = "Username must be at least 3 characters long";
        header("Location: create_teacher.php");
        exit();
    }
    
    if (strlen($teacher_username) > 30) {
        $_SESSION['error'] = "Username must not exceed 30 characters";
        header("Location: create_teacher.php");
        exit();
    }
    
    if (!preg_match("/^[a-zA-Z0-9_\-]+$/", $teacher_username)) {
        $_SESSION['error'] = "Username can only contain letters, numbers, hyphens and underscores";
        header("Location: create_teacher.php");
        exit();
    }
   
    $db = new SQLite3($db_file);
    # Check to see if the teacher already exists
    $query = "SELECT teacher_id FROM TEACHERS WHERE 
          teacher_email = :email OR 
          teacher_username = :username";
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        errorMessages("Error preparing check query", $db->lastErrorMsg());
    }
    
    $stmt->bindValue(":email", $teacher_email, SQLITE3_TEXT);
    $stmt->bindValue(":username", $teacher_username, SQLITE3_TEXT);
    $result = $stmt->execute();
    
    if (!$result) {
        errorMessages("Error executing check query", $db->lastErrorMsg());
    }
       
    $existing = $result->fetchArray();
    
    if ($existing) {
        $_SESSION['error'] = "A teacher with this email or username already exists";
        header("Location: create_teacher.php");
        $db->close();
        exit();
    }
    $unsafe_password = genPassword(6); 
    # Hash the password
    $password = password_hash($unsafe_password, PASSWORD_DEFAULT);
    
    # Insert new teacher
    $query = "
        INSERT INTO TEACHERS (
            teacher_name,
            teacher_email,
            teacher_username,
            teacher_password
        ) VALUES (
            :name,
            :email,
            :username,
            :password
        )
    ";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing insert query", $db->lastErrorMsg());
    }

    $stmt->bindValue(":name", $teacher_name, SQLITE3_TEXT);
    $stmt->bindValue(":email", $teacher_email, SQLITE3_TEXT);
    $stmt->bindValue(":username", $teacher_username, SQLITE3_TEXT);
    $stmt->bindValue(":password", $password, SQLITE3_TEXT);

    $result = $stmt->execute();
    if (!$result) {
        errorMessages("Error executing insert query", $db->lastErrorMsg());
    }
    
    # This section dowloads the information in a text file
  
    # Create credentials file content
    $credentials_content = "NextStep Teacher Account Credentials\n";
    $credentials_content .= "=====================================\n\n";
    $credentials_content .= "Name: " . $teacher_name . "\n";
    $credentials_content .= "Email: " . $teacher_email . "\n";
    $credentials_content .= "Username: " . $teacher_username . "\n";
    $credentials_content .= "Password: " . $unsafe_password . "\n\n";
    $credentials_content .= "Created: " . date('Y-m-d H:i:s') . "\n\n";
    
    # Set filename
    $filename = $teacher_username . "_credentials.txt";
    $_SESSION['new_teacher_credentials'] = $credentials_content;
    $_SESSION['new_teacher_filename']    = $teacher_username . "_credentials.txt";
    
    $success = "Teacher created successfully";
    $_SESSION['success'] = $success;
    header("Location: teachers.php");
    $db->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="css/style_navbar.css"/>
<link rel="stylesheet" href="css/style_page.css"/>
<title>NextStep - Create Teacher</title>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="page-box-wide">
<h2>Create Teacher</h2>
<?php flashMessages(); ?>

<form method="POST" action="create_teacher.php">
    <label for="teacher_name">Name:</label>
    <input type="text" id="teacher_name" name="teacher_name"/>
    <label for="teacher_email">Email:</label>
    <input type="email" id="teacher_email" name="teacher_email"/>
    <label for="teacher_username">Username:</label>
    <input type="text" id="teacher_username" name="teacher_username"/>
    <input type="submit" class="nav-btn" name="submit" value="Create Teacher">
</form>
</div>
</body>
</html>
