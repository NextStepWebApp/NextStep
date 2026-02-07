<?php
require_once "utils.php";
setup_checker();

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

function buildAlumniSearchQuery(SQLite3 $db, array $params = [])
{
    $where_clauses = [];
    $bind_params = [];

    if (!empty($params["name"])) {
        $where_clauses[] = "STUDENTS.students_name LIKE :name";
        $bind_params[":name"] = "%" . $params["name"] . "%";
    }

    if (!empty($params["email"])) {
        $where_clauses[] = "STUDENTS.students_email LIKE :email";
        $bind_params[":email"] = "%" . $params["email"] . "%";
    }

    if (!empty($params["phone"])) {
        $where_clauses[] = "STUDENTS.students_phone_number LIKE :phone";
        $bind_params[":phone"] = "%" . $params["phone"] . "%";
    }

    if (!empty($params["class"])) {
        $where_clauses[] = "STUDENTS.students_class_id = :class";
        $bind_params[":class"] = $params["class"];
    }

    if (!empty($params["country"])) {
        $where_clauses[] = "STUDENTS.students_country_id = :country";
        $bind_params[":country"] = $params["country"];
    }

    if (!empty($params["city"])) {
        $where_clauses[] = "STUDENTS.students_city_id = :city";
        $bind_params[":city"] = $params["city"];
    }

    if (!empty($params["school"])) {
        $where_clauses[] = "STUDENTS.students_school_id = :school";
        $bind_params[":school"] = $params["school"];
    }

    if (!empty($params["program"])) {
        $where_clauses[] = "STUDENTS.students_education_program_id = :program";
        $bind_params[":program"] = $params["program"];
    }

    if (!empty($params["status"])) {
        $where_clauses[] = "STUDENTS.students_status_id = :status";
        $bind_params[":status"] = $params["status"];
    }

    if (!empty($params["accessibility"])) {
        $where_clauses[] =
            "STUDENTS.students_accessibility_id = :accessibility";
        $bind_params[":accessibility"] = $params["accessibility"];
    }

    // Build WHERE clause
    $where_sql = "";
    if (!empty($where_clauses)) {
        $where_sql = "WHERE " . implode(" AND ", $where_clauses);
    }

    $query = "SELECT
        STUDENTS.students_id,
        STUDENTS.students_name,
        STUDENTS.students_email,
        STATUS.status_name,
        STUDENTS.students_created_date
    FROM STUDENTS
    JOIN STATUS ON STUDENTS.students_status_id = STATUS.status_id
    $where_sql
    ORDER BY STUDENTS.students_created_date DESC;";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing query $query", $db->lastErrorMsg());
    }

    foreach ($bind_params as $key => $value) {
        if (is_string($value)) {
            $stmt->bindValue($key, $value, SQLITE3_TEXT);
        } elseif (is_int($value)) {
            $stmt->bindValue($key, $value, SQLITE3_INTEGER);
        }
    }

    $count_query = "SELECT count(*) AS COUNT FROM STUDENTS $where_sql;";

    $stmt_count = $db->prepare($count_query);
    if (!$stmt_count) {
        errorMessages("Error preparing query", $db->lastErrorMsg());
    }

    foreach ($bind_params as $key => $value) {
        if (is_string($value)) {
            $stmt_count->bindValue($key, $value, SQLITE3_TEXT);
        } elseif (is_int($value)) {
            $stmt_count->bindValue($key, $value, SQLITE3_INTEGER);
        }
    }

    return [
        "data" => $stmt,
        "count" => $stmt_count,
    ];
}
