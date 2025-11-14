<?php
# Program to export the database to csv file

require_once "utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);

# Get access to the config file
$config = json_decode(file_get_contents($nextstep_config), true);
$db_file = $config["database_file_path"];

# Opening the database
try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

# Execute the query
$query = <<<EOF
SELECT
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
EOF;

$result = $db->query($query);
if (!$result) {
    errorMessages("Error executing query", $db->lastErrorMsg());
}

# Fetch all the data from the db
$rows = [];
while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $rows[] = $row;
}

# Build CSV content in memory
$csv_content = "";

# Creating the header
$header_list = ["time", "email", "name", "phone", "class", "country", "city", "school", "education_program", "status", "accessibility"];
$count_list = count($header_list) - 1;
$count = 0;

foreach ($header_list as $header_item) {
    if ($count != $count_list) {
        $csv_content .= "$header_item, ";
    } else {
        $csv_content .= "$header_item\n";
    }
    $count++;
}

# Loop that writes the data to the CSV content
foreach ($rows as $row) {
    $current_time = date('Y-m-d H:i:s'); # Get the current date and time
    $csv_content .= "$current_time, ";
    
    $count_tuple = count($row) - 1;
    $count = 0;
    
    foreach ($row as $item) {
        if ($count != $count_tuple) {
            $csv_content .= "$item, ";
        } else {
            $csv_content .= "$item\n";
        }
        $count++;
    }
}

$db->close();

# Store CSV content and filename in session
$_SESSION["export_csv_content"] = $csv_content;
$_SESSION["export_csv_filename"] = "nextstep_export_" . date('Y-m-d_His') . ".csv";

header("Location: download_success.php");
exit();
