<?php

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

// Used in search-filter.php
function getForeignKey(
    SQLite3 $db,
    string $query,
    string $paramName,
    $paramValue,
    $paramType,
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