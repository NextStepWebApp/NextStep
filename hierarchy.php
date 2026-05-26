<?php

require_once "utils.php";

$PERMISSION = [
    // Students
    "view_students" => ["USER", "SUPERUSER", "ADMIN"],
    "change_students" => ["SUPERUSER", "ADMIN"],
    "export_students" => ["ADMIN"],

    // Teachers
    "teachers_access" => ["ADMIN"],

    // Settings page
    "system_records" => ["ADMIN", "SUPERUSER"],
    "system_management" => ["ADMIN", "SYSADMIN"],
    "system_name" => ["ADMIN"],
    "system_smtp" => ["ADMIN", "SUPERUSER", "USER"],
    
    // Data/Map access
    "data_overview" => ["ADMIN", "SUPERUSER", "USER"],
];

function has_permission(string $permission, int $teacher_id, ?string $role_name = null)
{
    global $PERMISSION, $db_file;

    if ($role_name === null) {
        $db = new SQLite3($db_file);
        $stmt = $db->prepare("SELECT role_name FROM ROLES
                               JOIN TEACHERS ON TEACHERS.teacher_role_id = ROLES.role_id
                               WHERE TEACHERS.teacher_id = :id");
        $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        $role_name = $result["role_name"];
        $db->close();

        if (!$result) {
            return false;
        }
    }

    if (!in_array($role_name, $PERMISSION[$permission] ?? [])) {
        return false;
    }
    return true;
}

function require_permission(string $permission)
{
    global $PERMISSION, $db_file;

    $db = new SQLite3($db_file);
    $stmt = $db->prepare("SELECT role_name FROM ROLES
                            JOIN TEACHERS ON TEACHERS.teacher_role_id = ROLES.role_id
                            WHERE TEACHERS.teacher_id = :id");
    $stmt->bindValue(":id", $_SESSION["teacher_id"], SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    $db->close();

    if (
        !$result ||
        !in_array($result["role_name"], $PERMISSION[$permission] ?? [])
    ) {
        $_SESSION["error"] = "You do not have permission to do that";

        // Temporary put it to overview, because the home page is empty
        // And I will not really get message feedback
        header("Location: /NextStep/");
        exit();
    }
}
