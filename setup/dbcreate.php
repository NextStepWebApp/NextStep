<?php
# This piece the code is responsible to create the database for the nextstep application

require_once "utils.php";

# get acces to the config file 
$config = json_decode(file_get_contents($nextstep_config), true);

$db_file = $config["database_file_path"]; 

# Temporary!!
if (file_exists($db_file)) {
    unlink($db_file);
}

$db = new SQLite3($db_file); # database object
if (!$db) {
    die("Error creating database $db_file: " . $db->lastErrorMsg() . "\n");
} else {
    echo "Database created (or opened) successfully\n";
    $db->exec("PRAGMA foreign_keys = ON;"); # This is for foreign key support for the tables
}

#########################################
#          TEACHER SETUP
#########################################

# Create teachers table
$query = <<<EOF
      CREATE TABLE TEACHERS (
      teacher_id INTEGER PRIMARY KEY AUTOINCREMENT,
      teacher_email TEXT NOT NULL UNIQUE,
      teacher_name TEXT NOT NULL,
      teacher_username TEXT NOT NULL UNIQUE,
      teacher_password TEXT NOT NULL
      );

EOF;

# tablecreate is a function in utils.php
tableCreate($query, $db, "TEACHERS");

# genPassword is a funtion in utils.php
$unsafe_password = genPassword(8);
$password = password_hash($unsafe_password, PASSWORD_DEFAULT);

# query to insert the admin theacher to the db and a generated password
$query =
    "INSERT INTO TEACHERS (teacher_email, teacher_name, teacher_username, teacher_password) VALUES (:email, :name, :username, :password)";
$stmt = $db->prepare($query);
if (!$stmt) {
    echo "Error preparing query: " . $db->lastErrorMsg() . "\n";
}

$stmt->bindValue(":email", "admin@admin.com", SQLITE3_TEXT);
$stmt->bindValue(":name", "ADMIN", SQLITE3_TEXT);
$stmt->bindValue(":username", "ADMIN", SQLITE3_TEXT);
$stmt->bindValue(":password", $password, SQLITE3_TEXT);

$result = $stmt->execute();

if (!$result) {
    echo "Error inserting admin: " . $db->lastErrorMsg() . "\n";
} else {
    echo "ADMIN created and inserted successfully\n";

    # Get the location for where to save the generated password
    $location = $config["password_save_path"];

    # savefile is a funtion in utils.php
    savefile($location, "ADMIN-password.txt", $unsafe_password);
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
