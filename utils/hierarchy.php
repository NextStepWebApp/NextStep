<?php

function require_permission(string $permission)
{
    $permissions = [
        // Students
        "view_students" => ["USER", "SUPERUSER", "ADMIN"],
        "change_students" => ["SUPERUSER", "ADMIN"],
        "export_students" => ["ADMIN"],

        // Teachers
        "teachers_access" => ["ADMIN"],

        "system_records" => ["ADMIN", "SUPERUSER"],
        "system_management" => ["ADMIN", "SYSADMIN"],
    ];

    $role = $_SESSION["teacher_role"] ?? null;

    if (!$role || !in_array($role, $permissions[$permission] ?? [])) {
        $_SESSION["error"] = "You do not have permission to do that";
        header("Location: /NextStep/");
        exit();
    }
}
