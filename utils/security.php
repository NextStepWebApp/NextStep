<?php

function loginSecurity()
{
    if (!isset($_SESSION["teacher_username"])) {
        $_SESSION["error"] = "You are not logged in, please log in";
        header("Location: /NextStep/login.php");
        exit();
    }
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
