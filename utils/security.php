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
                header("Location: /NextStep/students/");
                exit();

            case "Teacher":
                $_SESSION["error"] = "Missing teacher_id";
                header("Location: /NextStep/teachers/");
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
                header("Location: /NextStep/students/");
                exit();

            case "Teacher":
                $_SESSION["error"] = "Invalid value for teacher_id";
                header("Location: /NextStep/teachers/");
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
