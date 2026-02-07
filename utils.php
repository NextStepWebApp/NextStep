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
$config_path = $nextstep_config["config_path"];
$config = json_decode(file_get_contents($config_path), true);

# This is the location to the branding json
$branding_path = $nextstep_config["branding_path"];
$branding = json_decode(file_get_contents($branding_path), true);

# Global color validate_teacher_name
# This is the theme that all the users will get by default
$color_theme_path = $nextstep_config["color_theme_path"];
$color_theme_system = json_decode(file_get_contents($color_theme_path), true);

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
function check_id(?string $id, string $group)
{
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
                errorMessages(
                    "Invalid group passed in check_id function",
                    "Group: $group",
                );
                exit();
        }
    }

    if (!is_numeric($id)) {
        switch ($group) {
            case "Students":
                $_SESSION["error"] = "Invalid value for student_id";
                header("Location: index.php");
                exit();

            case "Teacher":
                $_SESSION["error"] = "Invalid value for teacher_id";
                header("Location: teachers.php");
                exit();

            default:
                errorMessages(
                    "Invalid group passed in check_id function",
                    "Group: $group",
                );
                exit();
        }
    }
}

function full_students_database_query(SQLite3 $db)
{
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
    JOIN CLASS ON STUDENTS.students_class_id = CLASS.class_id
    JOIN COUNTRY ON STUDENTS.students_country_id = COUNTRY.country_id
    JOIN CITY ON STUDENTS.students_city_id = CITY.city_id
    JOIN SCHOOL ON STUDENTS.students_school_id = SCHOOL.school_id
    JOIN EDUCATION_PROGRAM ON STUDENTS.students_education_program_id = EDUCATION_PROGRAM.program_id
    JOIN STATUS ON STUDENTS.students_status_id = STATUS.status_id
    JOIN ACCESSIBILITY ON STUDENTS.students_accessibility_id = ACCESSIBILITY.accessibility_id
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

    return $row;
}

function get_or_create_foreign_key(
    $db,
    $table,
    $id_column,
    $name_column,
    $value,
) {
    // First, try to get existing foreign key
    $select_query = "SELECT $id_column FROM $table WHERE $name_column = :value";
    $stmt = $db->prepare($select_query);

    if (!$stmt) {
        errorMessages(
            "Error preparing select query in utils",
            $db->lastErrorMsg(),
        );
    }

    $stmt->bindValue(":value", $value, SQLITE3_TEXT);

    error_log("Attempting to insert into $table: '$value'");

    $result = $stmt->execute();

    if (!$result) {
        errorMessages(
            "Error executing select query in utils",
            $db->lastErrorMsg(),
        );
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
        errorMessages(
            "Error preparing insert query in utils",
            $db->lastErrorMsg(),
        );
    }

    $stmt->bindValue(":value", $value, SQLITE3_TEXT);
    $result = $stmt->execute();

    if (!$result) {
        errorMessages(
            "Error creating new record in utils",
            $db->lastErrorMsg(),
        );
    }

    return $db->lastInsertRowID();
}

function super_user_privilages(string $super_teacher)
{
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
function download_page_settings()
{
    $page_settings = ["teacher", "student", "admin"];

    if (
        isset($_SESSION["new_teacher_credentials"]) ||
        isset($_SESSION["new_teacher_filename"])
    ) {
        # Teacher download requires login
        loginSecurity();
        super_user_privilages($_SESSION["teacher_username"]);
        return $page_settings[0];
    } elseif (
        isset($_SESSION["export_csv_content"]) ||
        isset($_SESSION["export_csv_filename"])
    ) {
        # Student export requires login
        loginSecurity();
        super_user_privilages($_SESSION["teacher_username"]);
        return $page_settings[1];
    } elseif (
        isset($_SESSION["new_admin_credentials"]) ||
        isset($_SESSION["new_admin_filename"])
    ) {
        # Admin download during onboarding - no login required
        return $page_settings[2];
    } else {
        header("Location: /NextStep/");
        exit();
    }
}
# This is made to a function to avoid a lot of code repetition
# Input validation functions used in settings, edit and create page

function validate_teacher_name(
    SQLite3 $db,
    string $teacher_name,
    string $page,
    string $teacher_id = "",
) {
    if (strlen($teacher_name) < 2) {
        $_SESSION["error"] = "Name must be at least 2 characters long";

        switch ($page) {
            case "edit_teacher":
                header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
                $db->close();
                exit();

            case "settings":
                header("Location: settings.php");
                $db->close();
                exit();

            case "create_teacher":
                header("Location: create_teacher.php");
                $db->close();
                exit();

            default:
                header("Location: index.php");
                $db->close();
                exit();
        }
    }

    // Check maximum length
    if (strlen($teacher_name) > 50) {
        $_SESSION["error"] = "Name must not exceed 50 characters";

        switch ($page) {
            case "edit_teacher":
                header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
                $db->close();
                exit();

            case "settings":
                header("Location: settings.php");
                $db->close();
                exit();

            case "create_teacher":
                header("Location: create_teacher.php");
                $db->close();
                exit();

            default:
                header("Location: index.php");
                $db->close();
                exit();
        }
    }

    // Check valid characters
    if (!preg_match("/^[a-zA-Z\s\-'\.]+$/u", $teacher_name)) {
        $_SESSION["error"] = "Name or Username contains invalid characters";

        switch ($page) {
            case "edit_teacher":
                header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
                $db->close();
                exit();

            case "settings":
                header("Location: settings.php");
                $db->close();
                exit();

            case "create_teacher":
                header("Location: create_teacher.php");
                $db->close();
                exit();

            default:
                header("Location: index.php");
                $db->close();
                exit();
        }
    }
}

function validate_teacher_email(
    SQLite3 $db,
    string $teacher_email,
    string $page,
    string $teacher_id = "",
) {
    // Check email format
    if (!filter_var($teacher_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"] = "Invalid email format";

        switch ($page) {
            case "edit_teacher":
                header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
                $db->close();
                exit();

            case "settings":
                header("Location: settings.php");
                $db->close();
                exit();

            case "create_teacher":
                header("Location: create_teacher.php");
                $db->close();
                exit();

            default:
                header("Location: index.php");
                $db->close();
                exit();
        }
    }

    // Check maximum length
    if (strlen($teacher_email) > 50) {
        $_SESSION["error"] = "Email must not exceed 50 characters";

        switch ($page) {
            case "edit_teacher":
                header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
                $db->close();
                exit();

            case "settings":
                header("Location: settings.php");
                $db->close();
                exit();

            case "create_teacher":
                header("Location: create_teacher.php");
                $db->close();
                exit();

            default:
                header("Location: index.php");
                $db->close();
                exit();
        }
    }
}
function validate_student_name(
    SQLite3 $db,
    string $post_student_name,
    string $page,
    int $post_student_id = 0,
) {
    $student_name = trim($post_student_name);

    if (strlen($student_name) < 2) {
        $_SESSION["error"] = "Name must be at least 2 characters long";
        switch ($page) {
            case "edit_student":
                header(
                    "Location: /NextStep/students/edit_student.php?student_id=" .
                        $post_student_id,
                );
                break;
            case "create_student":
                header("Location: /NextStep/students/create_student.php");
                break;
            default:
                header("Location: /NextStep/students/index.php");
                break;
        }
        $db->close();
        exit();
    }

    if (strlen($student_name) > 50) {
        $_SESSION["error"] = "Name must not exceed 50 characters";
        switch ($page) {
            case "edit_student":
                header(
                    "Location: /NextStep/students/edit_student.php?student_id=" .
                        $post_student_id,
                );
                break;
            case "create_student":
                header("Location: /NextStep/students/create_student.php");
                break;
            default:
                header("Location: /NextStep/students/index.php");
                break;
        }
        $db->close();
        exit();
    }

    if (!preg_match("/^[a-zA-Z\s\-'\.]+$/u", $student_name)) {
        $_SESSION["error"] = "Name contains invalid characters";
        switch ($page) {
            case "edit_student":
                header(
                    "Location: /NextStep/students/edit_student.php?student_id=" .
                        $post_student_id,
                );
                break;
            case "create_student":
                header("Location: /NextStep/students/create_student.php");
                break;
            default:
                header("Location: /NextStep/students/index.php");
                break;
        }
        $db->close();
        exit();
    }
}
function validate_student_email(
    SQLite3 $db,
    string $post_student_email,
    string $page,
    int $post_student_id = 0,
) {
    $student_email = trim($post_student_email);

    if (!filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"] = "Invalid email format";
        switch ($page) {
            case "edit_student":
                header(
                    "Location: /NextStep/students/edit_student.php?student_id=" .
                        $post_student_id,
                );
                break;
            case "create_student":
                header("Location: /NextStep/students/create_student.php");
                break;
            default:
                header("Location: /NextStep/students/index.php");
                break;
        }
        $db->close();
        exit();
    }

    if (strlen($student_email) > 50) {
        $_SESSION["error"] = "Email must not exceed 50 characters";
        switch ($page) {
            case "edit_student":
                header(
                    "Location: /NextStep/students/edit_student.php?student_id=" .
                        $post_student_id,
                );
                break;
            case "create_student":
                header("Location: /NextStep/students/create_student.php");
                break;
            default:
                header("Location: /NextStep/students/index.php");
                break;
        }
        $db->close();
        exit();
    }
}

function validate_student_phone(
    SQLite3 $db,
    string $post_student_phone,
    string $page,
    int $post_student_id = 0,
) {
    $student_phone = trim($post_student_phone);
    $clean_phone = preg_replace("/[\s\-\(\)]/", "", $student_phone);

    if (!preg_match("/^\+?[0-9]{10,15}$/", $clean_phone)) {
        $_SESSION["error"] = "Phone number must be between 10-15 digits";
        switch ($page) {
            case "edit_student":
                header(
                    "Location: /NextStep/students/edit_student.php?student_id=" .
                        $post_student_id,
                );
                break;
            case "create_student":
                header("Location: /NextStep/students/create_student.php");
                break;
            default:
                header("Location: /NextStep/students/index.php");
                break;
        }
        $db->close();
        exit();
    }

    return $clean_phone;
}

# This is a function that gets the foreign key from the roles db for the teacher db

function get_foreign_key_roles(SQLite3 $db, string $role)
{
    $query = "SELECT role_id FROM ROLES WHERE role_name = :role";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        echo "Error preparing query for role qreation: " .
            $db->lastErrorMsg() .
            "\n";
    }
    $stmt->bindValue(":role", $role, SQLITE3_TEXT);
    $result = $stmt->execute();
    if (!$result) {
        errorMessages("Error selecting $role role", $db->lastErrorMsg());
    }
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $role_key = $row["role_id"];
    return $role_key;
}

# This function does onboarding

function setup_checker()
{
    $setup_config_path = "/var/lib/nextstepwebapp/setup.json";
    $setup_config = json_decode(file_get_contents($setup_config_path), true);
    $value = $setup_config["setup_value"];
    if ($value === 0) {
        header("Location: /NextStep/setup/onboarding.php");
        exit();
    }
}

function is_admin_checker(SQLite3 $db)
{
    # preperation to see if whe have a admin
    $query = "SELECT teacher_role_id
        FROM TEACHERS
        WHERE teacher_id = :teacher_id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(":teacher_id", $_SESSION["teacher_id"], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray();

    # Get the role id for admin (only admins can reset)
    $role_id = get_foreign_key_roles($db, "ADMIN");

    # compare
    if ((int) $row["teacher_role_id"] === (int) $role_id) {
        $method = "json"; # admin
    } else {
        $method = "sql"; # other users
    }
    return $method;
}

function color_theme_helper(SQLite3 $db, string $color_theme)
{
    $method = is_admin_checker($db);
    if ($method == "json") {
        return $color_theme;
    } elseif ($method == "sql") {
        $query = "SELECT THEME.theme_name FROM THEME
        JOIN TEACHERS ON THEME.theme_id = TEACHERS.teacher_theme_id
        WHERE TEACHERS.teacher_id = :teacher_id";
        $stmt = $db->prepare($query);
        $stmt->bindValue(
            ":teacher_id",
            $_SESSION["teacher_id"],
            SQLITE3_INTEGER,
        );
        $result = $stmt->execute();
        $row = $result->fetchArray();

        # The teacher does not yet have a own color selected (new teacher)
        if (!$row) {
            return $color_theme;
        }

        $teacher_color = $row[0];
        return $teacher_color;
    }
}

// Used in the search and filter page.
// getForeignKey helper to avoid code duplication
function getForeignKey(
    SQLite3 $db,
    string $query,
    string $paramName,
    $paramValue,
    int $paramType = SQLITE3_TEXT,
) {
    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing query $query", $db->lastErrorMsg());
        return null;
    }

    $stmt->bindValue($paramName, $paramValue, $paramType);

    $result = $stmt->execute();
    if (!$result) {
        errorMessages("Error executing query $query", $db->lastErrorMsg());
        return null;
    }

    $row = $result->fetchArray(SQLITE3_ASSOC);

    return $row ? array_values($row)[0] : null;
}
