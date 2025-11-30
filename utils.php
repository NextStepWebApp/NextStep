<?php
# This is the utils page for all the normal super_user_privilages
# The setup seciton does not include in this utils

# These are the file path config
# This is the only place that is allowed to have a specific path besides the config
$nextstep_config_path = "/etc/nextstepwebapp/nextstep_config.json";

$nextstep_config = json_decode(file_get_contents($nextstep_config_path), true);
 
# This is location to the database
$db_file = $nextstep_config["database_file_path"];

# These are the configs for validations
$config_path = "/etc/nextstepwebapp/config.json";
$config = json_decode(file_get_contents($config_path), true);

# This is the location to the branding json
$branding_path = "/etc/nextstepwebapp/branding.json";
$branding = json_decode(file_get_contents($branding_path), true);


function loginSecurity()
{
    if (!isset($_SESSION["teacher_username"])) {
        $_SESSION["error"] = "You are not logged in, please log in";
        header("Location: /NextStep/login.php");
        exit();
    }
}

function flashMessages()
{
    if (isset($_SESSION["error"])) {
        echo '<p class="flash" style="color: red;">' .
            htmlentities($_SESSION["error"]) .
            "</p>\n";
        unset($_SESSION["error"]);
    }
    if (isset($_SESSION["success"])) {
        echo '<p class="flash" style="color: green;">' .
            htmlentities($_SESSION["success"]) .
            "</p>\n";
        unset($_SESSION["success"]);
    }
}

function errorMessages(string $message, string $details)
{
    error_log("$message: $details");
    $_SESSION["error"] = $message;
    header("Location: failure.php");
    exit();
}


# Funtion that checks if the student_id is valid ( used in view, edit and delete)
function check_id(?string $id, string $group) {
        if (!isset($id)) {
            switch ($group) {
                case "Students":
                    $_SESSION["error"] = "Missing student_id";
                    header("Location: index.php");
                    exit();
                       
                case "Teacher":
                    $_SESSION["error"] = "Missing teacher_id";
                    header("Location: teachers.php");
                    exit();
                       
                default:
                    errorMessages("Invalid group passed in check_id function", "Group: $group");
                    exit();
            }
        }
           
        if (!is_numeric($id)) {
            switch ($group) {
                case "Students":
                    $_SESSION['error'] = "Invalid value for student_id";
                    header("Location: index.php");
                    exit();
                       
                case "Teacher":
                    $_SESSION['error'] = "Invalid value for teacher_id";
                    header("Location: teachers.php");
                    exit();
                       
                default:
                    errorMessages("Invalid group passed in check_id function", "Group: $group");
                    exit();
               }
        }
}


function full_students_database_query($db_file) {
    try {
        $db = new SQLite3($db_file);
    } catch (Exception $e) {
        errorMessages("Database connection failed", $e->getMessage());
    }

    $query = "SELECT
    STUDENTS.students_id,
    STUDENTS.students_name,
    STUDENTS.students_email,
    STUDENTS.students_phone_number,
    CLASS.class_name,
    COUNTRY.country_name,
    CITY.city_name,
    SCHOOL.school_name,
    EDUCATION_PROGRAM.program_name,
    STATUS.status_name,
    ACCESSIBILITY.accessibility_name,
    STUDENTS.students_created_date,
    STUDENTS.students_last_updated
    FROM STUDENTS
    LEFT JOIN CLASS ON STUDENTS.students_class_id = CLASS.class_id
    LEFT JOIN COUNTRY ON STUDENTS.students_country_id = COUNTRY.country_id
    LEFT JOIN CITY ON STUDENTS.students_city_id = CITY.city_id
    LEFT JOIN SCHOOL ON STUDENTS.students_school_id = SCHOOL.school_id
    LEFT JOIN EDUCATION_PROGRAM ON STUDENTS.students_education_program_id = EDUCATION_PROGRAM.program_id
    LEFT JOIN STATUS ON STUDENTS.students_status_id = STATUS.status_id
    LEFT JOIN ACCESSIBILITY ON STUDENTS.students_accessibility_id = ACCESSIBILITY.accessibility_id
    WHERE STUDENTS.students_id = :id;";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing query", $db->lastErrorMsg());
    }

    $stmt->bindValue(":id", $_GET["student_id"], SQLITE3_INTEGER);

    $results = $stmt->execute();
    if (!$results) {
        errorMessages("Error executing query", $db->lastErrorMsg());
    }
    $row = $results->fetchArray();
    $db->close();

    return $row;
}

function get_or_create_foreign_key($db, $table, $id_column, $name_column, $value) {
    // First, try to get existing foreign key
    $select_query = "SELECT $id_column FROM $table WHERE $name_column = :value";
    $stmt = $db->prepare($select_query);
    
    if (!$stmt) {
        errorMessages("Error preparing select query in utils", $db->lastErrorMsg());
    }
    
    $stmt->bindValue(":value", $value, SQLITE3_TEXT);
   
    error_log("Attempting to insert into $table: '$value'"); 
    
    $result = $stmt->execute();
    
    if (!$result) {
        errorMessages("Error executing select query in utils", $db->lastErrorMsg());
    }
    
    $row = $result->fetchArray();
    
    // If found, return the ID
    if ($row !== false) {
        return $row[0];
    }
    
    // If not found, create it
    $insert_query = "INSERT INTO $table ($name_column) VALUES (:value)";
    $stmt = $db->prepare($insert_query);
    
    if (!$stmt) {
        errorMessages("Error preparing insert query in utils", $db->lastErrorMsg());
    }
    
    $stmt->bindValue(":value", $value, SQLITE3_TEXT);
    $result = $stmt->execute();
    
    if (!$result) {
        errorMessages("Error creating new record in utils", $db->lastErrorMsg());
    }
    
    return $db->lastInsertRowID();
}


function super_user_privilages(string $super_teacher) {
    if ($super_teacher != "ADMIN") {
        header("Location: index.php");
        exit();
    }
}

# function that generates a password with alternating characters and numbers
function genPassword(int $length)
{
    $password = "";
    $letters = range("a", "z");
    for ($i = 0; $i < $length; $i++) {
        if ($i % 2 == 0) {
            $password .= rand(0, 9);
        } else {
            $index = rand(0, count($letters) - 1);
            $password .= $letters[$index];
        }
    }
    return $password;
}


# function that is used for the download pages to see what for type of download is asked
function download_page_settings() {
    $page_settings = ["teacher", "student"];
    
    if (isset($_SESSION["new_teacher_credentials"]) || isset($_SESSION["new_teacher_filename"])) {
        $settings = $page_settings[0];
        
    } else if (isset($_SESSION["export_csv_content"]) || isset($_SESSION["export_csv_filename"])) {
        $settings = $page_settings[1];
    } else {
        header("Location: /NextStep/");
        exit();
    }
    return $settings;
}


# This is made to a function to avoid a lot of code repetition
# Input validation functions used in settings, edit and create page

function validate_teacher_name(string $teacher_name, string $page, string $teacher_id = "") {
    if (strlen($teacher_name) < 2) {
        $_SESSION['error'] = "Name must be at least 2 characters long";
        
        switch ($page) {
            case "edit_teacher":
                header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
                exit();
                
            case "settings":
                header("Location: settings.php");
                exit();
                
            case "create_teacher":
                header("Location: create_teacher.php");
                exit();
                
            default:
                header("Location: index.php");
                exit();
        }
    }
    
    // Check maximum length
    if (strlen($teacher_name) > 50) {
        $_SESSION['error'] = "Name must not exceed 50 characters";
        
        switch ($page) {
            case "edit_teacher":
                header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
                exit();
                
            case "settings":
                header("Location: settings.php");
                exit();
                
            case "create_teacher":
                header("Location: create_teacher.php");
                exit();
                
            default:
                header("Location: index.php");
                exit();
        }
    }
    
    // Check valid characters
    if (!preg_match("/^[a-zA-Z\s\-'\.]+$/u", $teacher_name)) {
        $_SESSION['error'] = "Name or Username contains invalid characters";
        
        switch ($page) {
            case "edit_teacher":
                header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
                exit();
                
            case "settings":
                header("Location: settings.php");
                exit();
                
            case "create_teacher":
                header("Location: create_teacher.php");
                exit();
                
            default:
                header("Location: index.php");
                exit();
        }
    }
}

function validate_teacher_email(string $teacher_email, string $page, string $teacher_id = "") {
    // Check email format
    if (!filter_var($teacher_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        
        switch ($page) {
            case "edit_teacher":
                header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
                exit();
                
            case "settings":
                header("Location: settings.php");
                exit();
                
            case "create_teacher":
                header("Location: create_teacher.php");
                exit();
                
            default:
                header("Location: index.php");
                exit();
        }
    }
    
    // Check maximum length
    if (strlen($teacher_email) > 50) {
        $_SESSION['error'] = "Email must not exceed 50 characters";
        
        switch ($page) {
            case "edit_teacher":
                header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
                exit();
                
            case "settings":
                header("Location: settings.php");
                exit();
                
            case "create_teacher":
                header("Location: create_teacher.php");
                exit();
                
            default:
                header("Location: index.php");
                exit();
        }
    }
}


# This is a function that gets the foreign key from the roles db for the teacher db

function get_foreign_key_roles (SQLite3 $db, string $role) {
    $query = "SELECT role_id FROM ROLES WHERE role_name = :role";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        echo "Error preparing query for role qreation: " . $db->lastErrorMsg() . "\n";
    }
    $stmt->bindValue(":role", $role, SQLITE3_TEXT);
    $result = $stmt->execute();
    if (!$result) {
        errorMessages("Error selecting $role role", $db->lastErrorMsg());
    }     
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $role_key = $row['role_id'];
    return $role_key;
}


# This function does onboarding

function setup_checker() {
    $setup_config_path = "/etc/nextstepwebapp/nextstep_config.json";
    $setup_config = json_decode(file_get_contents($setup_config_path), true);
    $value = $setup_config["setup_value"];
    if ($value === 0) {
        header("Location: /NextStep/setup/onboarding.php");
        exit();
    }
}
