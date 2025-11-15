<?php
require_once "utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="css/style_navbar.css"/>
<link rel="stylesheet" href="css/style_page.css"/>
<title>NextStep - Student</title>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="page-box">
    <?php flashMessages(); ?>
    <a href="create_student.php" class="simple-btn">Create Student</a>
    <a href="import_students.php" class="simple-btn">Import Data</a>
    <a href="export_students.php" class="simple-btn">Export Data</a>
    
    <?php
    if (isset($_SESSION['import_results_python'])):
        $output = $_SESSION['import_results_python'];
        echo "<div style='margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 5px;'>";
        echo "<pre style='margin: 0; white-space: pre-wrap;'>";
        foreach ($output as $line) {
            echo htmlspecialchars($line) . "\n";
        }
        echo "</pre></div>";
        unset($_SESSION['import_results_python']);
    endif;
    ?>
</div>
<script src="js/script.js"></script>
</body>
</html>
