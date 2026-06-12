<?php

// Email data for email type
define("EMAIL_NORMAL", 0);
define("EMAIL_FULL", 1);

// For verification status
define("EMAIL_UNVERIFIED", 0);
define("EMAIL_VERIFIED", 1);

// For email sending status
define("EMAIL_FAILED", 0);
define("EMAIL_SUCCESS", 1);

// Max queue attempts
define("QUEUE_LIMIT", 3);

function query_students(SQLite3 $db, array $search, ?string $mode = "All") {
    $conditions = [];
    $params     = [];

    $like_fields = [
        "students_name",
        "students_email",
    ];

    $name_fields = [
        "class_name"         => "class_name",
        "country_name"       => "country_name",
        "city_name"          => "city_name",
        "school_name"        => "school_name",
        "program_name"       => "program_name",
        "status_name"        => "status_name",
        "accessibility_name" => "accessibility_name",
    ];

    foreach ($like_fields as $field) {
        if (!empty($search[$field])) {
            $placeholder  = ":" . $field;
            $conditions[] = "STUDENTS.$field LIKE $placeholder";
            $params[$placeholder] = ["value" => "%" . $search[$field] . "%", "type" => SQLITE3_TEXT];
        }
    }

    foreach ($name_fields as $key => $column) {
        if (!empty($search[$key])) {
            $placeholder  = ":" . $key;
            $conditions[] = "$column = $placeholder";
            $params[$placeholder] = ["value" => $search[$key], "type" => SQLITE3_TEXT];
        }
    }

    if (!empty($search["date_from"])) {
        $conditions[] = "STUDENTS.students_created_date >= :date_from";
        $params[":date_from"] = ["value" => $search["date_from"], "type" => SQLITE3_TEXT];
    }
    
    if (!empty($search["date_to"])) {
        $conditions[] = "STUDENTS.students_created_date <= :date_to";
        $params[":date_to"] = ["value" => $search["date_to"], "type" => SQLITE3_TEXT];
    }

    $where_clause = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

    $joins = "JOIN STATUS            ON STUDENTS.students_status_id            = STATUS.status_id
              JOIN CLASS             ON STUDENTS.students_class_id             = CLASS.class_id
              JOIN COUNTRY           ON STUDENTS.students_country_id           = COUNTRY.country_id
              JOIN CITY              ON STUDENTS.students_city_id              = CITY.city_id
              JOIN SCHOOL            ON STUDENTS.students_school_id            = SCHOOL.school_id
              JOIN EDUCATION_PROGRAM ON STUDENTS.students_education_program_id = EDUCATION_PROGRAM.program_id
              JOIN ACCESSIBILITY     ON STUDENTS.students_accessibility_id     = ACCESSIBILITY.accessibility_id";

    if ($mode === "count") {
        $sql = "SELECT COUNT(*) as total FROM STUDENTS $joins $where_clause;";
    } elseif ($mode === "id") {
        $sql = "SELECT students_id
                FROM STUDENTS $joins $where_clause
                ORDER BY STUDENTS.students_name ASC;";
    } else {
        // All
        $sql = "SELECT 
            STUDENTS.students_id,
            STUDENTS.students_name,
            STUDENTS.students_email,
            STUDENTS.students_created_date,
            STATUS.status_name,
            SCHOOL.school_name,
            EDUCATION_PROGRAM.program_name
            FROM STUDENTS $joins $where_clause
            ORDER BY STUDENTS.students_name ASC;";
    }

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        errorMessages("Error preparing student query", $db->lastErrorMsg());
    }

    foreach ($params as $key => $p) {
        $stmt->bindValue($key, $p["value"], $p["type"]);
    }

    $result = $stmt->execute();
    if (!$result) {
        errorMessages("Error executing student query", $db->lastErrorMsg());
    }

    if ($mode === "count") {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return (int) ($row["total"] ?? 0);
    }

    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

