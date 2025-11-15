<?php
require_once "utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);

# Get the python import program file path from the NextStep config
$config = json_decode(file_get_contents($nextstep_config), true);
$python_script = $config["python_import_path"];

if (isset($_POST["submit"])) {
    # Handle csv file upload
    if (isset($_FILES["csv_file"]["name"])) {
        $filename = explode(".", $_FILES["csv_file"]["name"]); 
        if ($filename[1] == "csv") {
            $upload_file_path = $_FILES["csv_file"]["tmp_name"];
            
            if (!file_exists($upload_file_path) || filesize($upload_file_path) == 0) {
                $_SESSION['error'] = "Uploaded file is empty or doesn't exist";
                header("Location: import_students.php");
                exit();
            }
            
            # Python preparation
            $safe_script_path = escapeshellarg($python_script);
            $safe_file_path = escapeshellarg($upload_file_path);
            $python_command = "python3 $safe_script_path $safe_file_path 2>&1";
                       
            $output = [];
            $return_code = 0;
            
            # This will run the import.py program and populate the database
            exec($python_command, $output, $return_code);
                       
            if ($return_code === 0) {  
                $_SESSION['success'] = "Import completed successfully!";
                $_SESSION['import_results_python'] = $output;
                header("Location: students.php");
                exit();
            } else {
                $_SESSION['error'] = "Import failed. Check output below.";
                $_SESSION['import_results_python'] = $output;
                header("Location: students.php");
                exit();
            }
            
        } else {
            $_SESSION['error'] = "File is not a csv filetype";
            header("Location: import_students.php");
            exit();
        }
    } else {
        $_SESSION["error"] = "Error no csv file";
        header("Location: import_students.php");
        exit();
    }
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
    <title>NextStep - Import Students</title>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="page-box-wide">
    <h2>Import Students from CSV</h2>
    <?php flashMessages(); ?>
        <h3>CSV File Format Rules</h3>
        <button class="simple-btn" data-open-modal>Rules</button>
        <dialog data-modal class="wide-modal">
            <p>Your CSV file must have the following columns in this exact order:</p>
            <p>time, email, name, phone, class, country, city, school, education_program, status, accessibility</p>
            <p><strong>Note:</strong> The first row (header) will be skipped automatically.</p>
            <p>All values will be validated against your configuration files</p>
            <button class="simple-btn" data-close-modal>Close</button>
        </dialog>
   
    <form method="POST" action="import_students.php" enctype="multipart/form-data">
        <label for="csv_file">Select CSV File:</label>
        <input type="file" name="csv_file" accept=".csv" required/>
        
        <div class="button-container">
            <input type="submit" class="simple-btn" name="submit" value="Import Students">
            <a href="students.php" class="simple-btn cancel-btn">Cancel</a>
        </div>
    </form>
</div>
<script src="js/script.js"></script>
</body>
</html>
