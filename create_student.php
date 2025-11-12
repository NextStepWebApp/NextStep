<?php
require_once "utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);

$db = new SQLite3($db_file);

// This loads the config json file
$config = json_decode(file_get_contents('config/config.json'), true);

$accessibility = $config['accessibility'];
$city = $config['city'];
$class = $config['class'];
$country = $config['country'];
$education = $config['education'];
$schools = $config['school'];
$status = $config['status'];


if (isset($_POST["submit"])) {

    // Check if all fields are filled
    if (empty($_POST["student_name"]) || empty($_POST["student_email"]) ||
          empty($_POST["student_phone"]) || empty($_POST["class_name"]) ||
          empty($_POST["country_name"]) || empty($_POST["city_name"]) ||
          empty($_POST["school_name"]) || empty($_POST["program_name"]) ||
          empty($_POST["status"]) || empty($_POST["accessibility"])) {

        $_SESSION['error'] = "All fields are required";
        header("Location: create_student.php");
        exit();
    }
    
    # This section will be validations of name, email and phone
    
    # Validate student name
    $student_name = trim($_POST["student_name"]);
    if (strlen($student_name) < 2) {
        $_SESSION['error'] = "Name must be at least 2 characters long";
        header("Location: create_student.php");
        exit();
    }
    
    if (strlen($student_name) > 50) {
        $_SESSION['error'] = "Name must not exceed 50 characters";
        header("Location: create_student.php");
        exit();
    }
    
    if (!preg_match("/^[a-zA-Z\s\-'\.]+$/u", $student_name)) {
        $_SESSION['error'] = "Name contains invalid characters";
        header("Location: create_student.php");
        exit();
    }
    
    # Validate email
    $student_email = trim($_POST["student_email"]);
    if (!filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: create_student.php");
        exit();
    }
    
    if (strlen($student_email) > 50) {
        $_SESSION['error'] = "Email must not exceed 50 characters";
        header("Location: create_student.php");
        exit();
    }
    
    # Validate phone number
    $student_phone = trim($_POST["student_phone"]);
    # Remove common formatting characters
    $clean_phone = preg_replace('/[\s\-\(\)\+]/', '', $student_phone);
    
    if (!preg_match("/^[0-9]{10,15}$/", $clean_phone)) {
        $_SESSION['error'] = "Phone number must be between 10-15 digits";
        header("Location: create_student.php");
        exit();
    }
    
    # Store cleaned phone number for database insertion
    $_POST["student_phone"] = $clean_phone;
    
    # Check to see if the student already exists
      $query = "SELECT students_id FROM STUDENTS WHERE 
          students_email = :email OR 
          students_name = :name OR 
          students_phone_number = :phone";
      $stmt = $db->prepare($query);
      
      if (!$stmt) {
          errorMessages("Error preparing insert query check", $db->lastErrorMsg());
      }
      
      $stmt->bindValue(":email", $_POST["student_email"], SQLITE3_TEXT);
      $stmt->bindValue(":name", $_POST["student_name"], SQLITE3_TEXT);
      $stmt->bindValue(":phone", $_POST["student_phone"], SQLITE3_TEXT);
      $result = $stmt->execute();
      
      if (!$result) {
             errorMessages("Error creating new record in check", $db->lastErrorMsg());
      }
         
      $existing = $result->fetchArray();
      
      if ($existing) {
          $_SESSION['error'] = "A student with this name, email, or phone number already exists";
          header("Location: create_student.php");
          exit();
      }

    // Validate all dropdown values against config
    if (!in_array($_POST["class_name"], $class) ||
        !in_array($_POST["country_name"], $country) ||
        !in_array($_POST["city_name"], $city) ||
        !in_array($_POST["school_name"], $schools) ||
        !in_array($_POST["program_name"], $education) ||
        !in_array($_POST["status"], $status) ||
        !in_array($_POST["accessibility"], $accessibility)) {
        $_SESSION['error'] = "Invalid selection detected";
        error_log("Config validation error - invalid dropdown value submitted");
        header("Location: students.php");
        exit();
    }
     # Here are the queries to get the foriegn keys

    # This is a function from utils.php
    # This funtion is to get foriegn key from the tables
    $result_class_id = get_or_create_foreign_key($db, "CLASS", "class_id", "class_name", $_POST["class_name"]);
    $result_country_id = get_or_create_foreign_key($db, "COUNTRY", "country_id", "country_name", $_POST["country_name"]);
    $result_city_id = get_or_create_foreign_key($db, "CITY", "city_id", "city_name", $_POST["city_name"]);
    $result_school_id = get_or_create_foreign_key($db, "SCHOOL", "school_id", "school_name", $_POST["school_name"]);
    $result_program_id = get_or_create_foreign_key($db, "EDUCATION_PROGRAM", "program_id", "program_name", $_POST["program_name"]);
    $result_status_id = get_or_create_foreign_key($db, "STATUS", "status_id", "status_name", $_POST["status"]);
    $result_accessibility_id = get_or_create_foreign_key($db, "ACCESSIBILITY", "accessibility_id", "accessibility_name", $_POST["accessibility"]);

    $query = "
        INSERT INTO STUDENTS (
            students_name,
            students_email,
            students_phone_number,
            students_class_id,
            students_country_id,
            students_city_id,
            students_school_id,
            students_education_program_id,
            students_status_id,
            students_accessibility_id,
            students_created_date,
            students_last_updated
        ) VALUES (
            :name,
            :email,
            :phone,
            :class_id,
            :country_id,
            :city_id,
            :school_id,
            :program_id,
            :status_id,
            :accessibility_id,
            CAST(strftime('%Y', 'now') AS INTEGER),
            CAST(strftime('%s', 'now') AS INTEGER)
        )
    ";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing query in main", $db->lastErrorMsg());
    }

    $stmt->bindValue(":name", $_POST["student_name"], SQLITE3_TEXT);
    $stmt->bindValue(":email", $_POST["student_email"], SQLITE3_TEXT);
    $stmt->bindValue(":phone", $_POST["student_phone"], SQLITE3_TEXT);
    $stmt->bindValue(":class_id", $result_class_id, SQLITE3_INTEGER);
    $stmt->bindValue(":country_id", $result_country_id, SQLITE3_INTEGER);
    $stmt->bindValue(":city_id", $result_city_id, SQLITE3_INTEGER);
    $stmt->bindValue(":school_id", $result_school_id, SQLITE3_INTEGER);
    $stmt->bindValue(":program_id", $result_program_id, SQLITE3_INTEGER);
    $stmt->bindValue(":status_id", $result_status_id, SQLITE3_INTEGER);
    $stmt->bindValue(":accessibility_id",  $result_accessibility_id, SQLITE3_INTEGER);

    $result = $stmt->execute();
    if (!$result) {
        errorMessages("Error executing query in main", $db->lastErrorMsg());
    }
    $success = "Student created successfully";
       $_SESSION['success'] = $success;
       header("Location: students.php");
       exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="css/style_navbar.css"/>
<link rel="stylesheet" href="css/style_page.css"/>
<title>NextStep - Create Student</title>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="page-box-wide">
<h2>Create Student</h2>
<?php flashMessages(); ?>

<form method="POST" action="create_student.php">
    <label for="student_name">Name:</label>
    <input type="text" id="student_name" name="student_name"/>

    <label for="student_email">Email:</label>
    <input type="email" id="student_email" name="student_email"/>

    <label for="student_phone">Phone number:</label>
    <input type="tel" id="student_phone" name="student_phone"/>

    <label for="class_name">Class name:</label>
    <select id="class_name" name="class_name">
        <option value="">Select Class</option>
        <?php foreach ($class as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>">
                <?= htmlspecialchars($c) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="country_name">Country:</label>
    <select id="country_name" name="country_name">
        <option value="">Select Country</option>
        <?php foreach ($country as $cnt): ?>
            <option value="<?= htmlspecialchars($cnt) ?>">
                <?= htmlspecialchars($cnt) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="city_name">City:</label>
    <select id="city_name" name="city_name">
        <option value="">Select City</option>
        <?php foreach ($city as $cty): ?>
            <option value="<?= htmlspecialchars($cty) ?>">
                <?= htmlspecialchars($cty) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="school_name">School:</label>
    <select id="school_name" name="school_name">
        <option value="">Select School</option>
        <?php foreach ($schools as $school): ?>
            <option value="<?= htmlspecialchars($school) ?>">
                <?= htmlspecialchars($school) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="program_name">Program:</label>
    <select id="program_name" name="program_name">
        <option value="">Select Program</option>
        <?php foreach ($education as $edu): ?>
            <option value="<?= htmlspecialchars($edu) ?>">
                <?= htmlspecialchars($edu) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="status">Status:</label>
    <select id="status" name="status">
        <option value="">Select Status</option>
        <?php foreach ($status as $stat): ?>
            <option value="<?= htmlspecialchars($stat) ?>">
                <?= htmlspecialchars($stat) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="accessibility">Accessibility:</label>
    <select id="accessibility" name="accessibility">
        <option value="">Select</option>
        <?php foreach ($accessibility as $acc): ?>
            <option value="<?= htmlspecialchars($acc) ?>">
                <?= htmlspecialchars($acc) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="button-container">
        <input type="submit" class="nav-btn" name="submit" value="Create Student">
        <a href="students.php" class="nav-btn cancel-btn">Cancel</a>
    </div>
</form>
</div>
<script src="js/script.js"></script>
</body>
</html>
