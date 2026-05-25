<?php
# This piece of code is responsible to create the database for the nextstep application
session_start(); # This is to give the account credentials to the download functionality
require_once "utils.php";

# get acces to the config file
$config = json_decode(file_get_contents($nextstep_config), true); # $nextstep_config comes from utils
# Filepath to the setup.json
$setup_config_path = $config["setup_config_path"];
if (!file_exists($setup_config_path)) {
    die("setup.json not found at $setup_config_path\n");
}
$setup_config = json_decode(file_get_contents($setup_config_path), true);

# Check if the value is 0; if not, redirect and exit
if (
    !isset($setup_config["setup_value"]) ||
    $setup_config["setup_value"] !== 0
) {
    header("Location: /NextStep/");
    exit();
}

$db_file = $config["database_file_path"];

# Reset
if (file_exists($db_file)) {
    unlink($db_file);
}

$db = new SQLite3($db_file); # database object
if (!$db) {
    die("Error creating database $db_file: " . $db->lastErrorMsg() . "\n");
} else {
    error_log("Database created (or opened) successfully\n");
    $db->exec("PRAGMA foreign_keys = ON;"); # This is for foreign key support for the tables
}

#########################################
#          TEACHER SETUP
#########################################

# Create teacher role table
# This table will have the roles
$query = <<<EOF
      CREATE TABLE ROLES (
      role_id INTEGER PRIMARY KEY AUTOINCREMENT,
      role_name TEXT NOT NULL UNIQUE
      );
EOF;
tableCreate($query, $db, "ROLES");

# This part creates the roles that are going to be used
$roles_list = ["ADMIN", "USER", "SUPERUSER", "SYSADMIN"];

foreach ($roles_list as $role_list) {
    $query = "INSERT INTO ROLES (role_name) VALUES (:role)";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        error_log(
            "Error preparing query for role qreation: " .
                $db->lastErrorMsg() .
                "\n",
        );
    }
    $stmt->bindValue(":role", $role_list, SQLITE3_TEXT);
    $result = $stmt->execute();
    if (!$result) {
        error_log(
            " - Error inserting $role_list role: " . $db->lastErrorMsg() . "\n",
        );
    } else {
        error_log(" - Role $role_list  created and inserted successfully\n");
    }
}

# Create teacher color theme table
# This table will have the color themes for teachers not being admin
$query = <<<EOF
      CREATE TABLE THEME (
      theme_id INTEGER PRIMARY KEY AUTOINCREMENT,
      theme_name TEXT NOT NULL UNIQUE
      );
EOF;
tableCreate($query, $db, "THEME");

# Create smtp settings table for teachers
$query = <<<EOF
      CREATE TABLE SMTP (
      smtp_id INTEGER PRIMARY KEY AUTOINCREMENT,
      teacher_id INTEGER NOT NULL,
      smtp_email TEXT NOT NULL,
      smtp_host TEXT NOT NULL,
      smtp_port INTEGER NOT NULL,
      smtp_password TEXT NOT NULL,
      FOREIGN KEY (teacher_id) REFERENCES TEACHERS(teacher_id) ON DELETE CASCADE
);
EOF;
tablecreate($query, $db, "SMTP");

# Create teachers table
$query = <<<EOF
      CREATE TABLE TEACHERS (
      teacher_id INTEGER PRIMARY KEY AUTOINCREMENT,
      teacher_name TEXT NOT NULL,
      teacher_username TEXT NOT NULL UNIQUE,
      teacher_password TEXT NOT NULL,
      teacher_theme_id INTEGER,
      teacher_role_id INTEGER NOT NULL,
      FOREIGN KEY (teacher_theme_id) REFERENCES THEME(theme_id),
      FOREIGN KEY (teacher_role_id) REFERENCES ROLES(role_id)
      );
EOF;
# The teacher role is table for permission roles

# tablecreate is a function in utils.php
tableCreate($query, $db, "TEACHERS");

# genPassword is a funtion in utils.php
$unsafe_password = genPassword(8);
$password = password_hash($unsafe_password, PASSWORD_DEFAULT);

# Get foreign key for admin role
$query = "SELECT role_id FROM ROLES WHERE role_name = :role";
$stmt = $db->prepare($query);
if (!$stmt) {
    error_log(
        "Error preparing query for getting foreign key for admin: " .
            $db->lastErrorMsg() .
            "\n",
    );
}
$stmt->bindValue(":role", "ADMIN", SQLITE3_TEXT);
$result = $stmt->execute();
if (!$result) {
    error_log(
        " - Error selecting admin foreign key: " . $db->lastErrorMsg() . "\n",
    );
} else {
    error_log(" - Succes selecting foreign key\n");
}
$row = $result->fetchArray(SQLITE3_ASSOC);
$role_admin_key = $row["role_id"];

# query to insert the admin theacher to the db and a generated password
$query = <<<EOF
INSERT INTO TEACHERS (teacher_name, teacher_username, teacher_password, teacher_role_id)
VALUES (:name, :username, :password, :role);
EOF;

$stmt = $db->prepare($query);
if (!$stmt) {
    error_log("Error preparing query: " . $db->lastErrorMsg() . "\n");
}

$stmt->bindValue(":name", "ADMIN", SQLITE3_TEXT);
$stmt->bindValue(":username", "ADMIN", SQLITE3_TEXT);
$stmt->bindValue(":password", $password, SQLITE3_TEXT);
$stmt->bindValue(":role", $role_admin_key, SQLITE3_INTEGER);

$result = $stmt->execute();

if (!$result) {
    error_log("Error inserting admin: " . $db->lastErrorMsg() . "\n");
} else {
    error_log("ADMIN created and inserted successfully\n");
}

#########################################
#          STUDENT SETUP
#########################################

# Create class_secondary_school table
$query = <<<EOF
      CREATE TABLE CLASS (
      class_id INTEGER PRIMARY KEY AUTOINCREMENT,
      class_name TEXT NOT NULL UNIQUE
      );
EOF;

tableCreate($query, $db, "CLASS");

# Create city and country table
# These tables are for the school table

$query = <<<EOF
      CREATE TABLE COUNTRY (
      country_id INTEGER PRIMARY KEY AUTOINCREMENT,
      country_name TEXT NOT NULL UNIQUE
      );
EOF;

tableCreate($query, $db, "COUNTRY");
$query = <<<EOF
      CREATE TABLE CITY (
      city_id INTEGER PRIMARY KEY AUTOINCREMENT,
      city_name TEXT NOT NULL UNIQUE
      );
EOF;

tableCreate($query, $db, "CITY");

# Create school table
$query = <<<EOF
      CREATE TABLE SCHOOL (
      school_id INTEGER PRIMARY KEY AUTOINCREMENT,
      school_name TEXT NOT NULL UNIQUE
      );
EOF;

tableCreate($query, $db, "SCHOOL");

# Create education program table
$query = <<<EOF
      CREATE TABLE EDUCATION_PROGRAM (
      program_id INTEGER PRIMARY KEY AUTOINCREMENT,
      program_name TEXT NOT NULL UNIQUE
      );
EOF;

tableCreate($query, $db, "EDUCATION_PROGRAM");

# Create status table
$query = <<<EOF
      CREATE TABLE STATUS (
      status_id INTEGER PRIMARY KEY AUTOINCREMENT,
      status_name TEXT NOT NULL UNIQUE
      );
EOF;

tableCreate($query, $db, "STATUS");

# Create accessibility table
$query = <<<EOF
      CREATE TABLE ACCESSIBILITY (
      accessibility_id INTEGER PRIMARY KEY AUTOINCREMENT,
      accessibility_name TEXT NOT NULL UNIQUE
      );
EOF;

tableCreate($query, $db, "ACCESSIBILITY");

# Create students table
$query = <<<EOF
      CREATE TABLE STUDENTS (
      students_id INTEGER PRIMARY KEY AUTOINCREMENT,
      students_name TEXT NOT NULL UNIQUE,
      students_email TEXT NOT NULL UNIQUE,
      students_phone_number TEXT UNIQUE,
      students_class_id INTEGER,
      students_country_id INTEGER,
      students_city_id INTEGER,
      students_school_id INTEGER,
      students_education_program_id INTEGER,
      students_status_id INTEGER,
      students_accessibility_id INTEGER,
      students_created_date INTEGER NOT NULL,
      students_last_updated INTEGER NOT NULL,
      FOREIGN KEY (students_class_id) REFERENCES CLASS(class_id),
      FOREIGN KEY (students_country_id) REFERENCES COUNTRY(country_id),
      FOREIGN KEY (students_city_id) REFERENCES CITY(city_id),
      FOREIGN KEY (students_school_id) REFERENCES SCHOOL(school_id),
      FOREIGN KEY (students_education_program_id) REFERENCES EDUCATION_PROGRAM(program_id),
      FOREIGN KEY (students_status_id) REFERENCES STATUS(status_id),
      FOREIGN KEY (students_accessibility_id) REFERENCES ACCESSIBILITY(accessibility_id)

      );
EOF;

tableCreate($query, $db, "STUDENTS");

$db->close();

# Change the value 0 in the setup.json to 1
$setup_config["setup_value"] = 1;
# Save the updated JSON back to the file
if (
    file_put_contents(
        $setup_config_path,
        json_encode($setup_config, JSON_PRETTY_PRINT),
    ) === false
) {
    error_log("Failed to update setup.json\n");
}

# Download the credentials
# Create credentials file content
$credentials_content = "NextStep ADMIN Account\n";
$credentials_content .= "=========================================\n\n";
$credentials_content .= "Username: " . "ADMIN" . "\n";
$credentials_content .= "Password: " . $unsafe_password . "\n\n";

$_SESSION["new_admin_credentials"] = $credentials_content;
$_SESSION["new_admin_filename"] = "ADMIN-login-credentials.txt";
header("Location: /NextStep/download/download_success.php");
exit();
