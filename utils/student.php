<?php

function validate_student_name(
    SQLite3 $db,
    string $post_student_name,
    string $page,
    int $post_student_id = 0,
) {
    $student_name = trim($post_student_name);

    if (strlen($student_name) < 2) {
        $_SESSION["error"] = "Name must be at least 2 characters long";
        switch ($page) {
            case "edit_student":
                header(
                    "Location: /NextStep/students/edit_student.php?student_id=" .
                        $post_student_id,
                );
                break;
            case "create_student":
                header("Location: /NextStep/students/create_student.php");
                break;
            default:
                header("Location: /NextStep/students/index.php");
                break;
        }
        $db->close();
        exit();
    }

    if (strlen($student_name) > 50) {
        $_SESSION["error"] = "Name must not exceed 50 characters";
        switch ($page) {
            case "edit_student":
                header(
                    "Location: /NextStep/students/edit_student.php?student_id=" .
                        $post_student_id,
                );
                break;
            case "create_student":
                header("Location: /NextStep/students/create_student.php");
                break;
            default:
                header("Location: /NextStep/students/index.php");
                break;
        }
        $db->close();
        exit();
    }

    if (!preg_match("/^[a-zA-Z\s\-'\.]+$/u", $student_name)) {
        $_SESSION["error"] = "Name contains invalid characters";
        switch ($page) {
            case "edit_student":
                header(
                    "Location: /NextStep/students/edit_student.php?student_id=" .
                        $post_student_id,
                );
                break;
            case "create_student":
                header("Location: /NextStep/students/create_student.php");
                break;
            default:
                header("Location: /NextStep/students/index.php");
                break;
        }
        $db->close();
        exit();
    }
}
function validate_student_email(
    SQLite3 $db,
    string $post_student_email,
    string $page,
    int $post_student_id = 0,
) {
    $student_email = trim($post_student_email);

    if (!filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"] = "Invalid email format";
        switch ($page) {
            case "edit_student":
                header(
                    "Location: /NextStep/students/edit_student.php?student_id=" .
                        $post_student_id,
                );
                break;
            case "create_student":
                header("Location: /NextStep/students/create_student.php");
                break;
            default:
                header("Location: /NextStep/students/index.php");
                break;
        }
        $db->close();
        exit();
    }

    if (strlen($student_email) > 50) {
        $_SESSION["error"] = "Email must not exceed 50 characters";
        switch ($page) {
            case "edit_student":
                header(
                    "Location: /NextStep/students/edit_student.php?student_id=" .
                        $post_student_id,
                );
                break;
            case "create_student":
                header("Location: /NextStep/students/create_student.php");
                break;
            default:
                header("Location: /NextStep/students/index.php");
                break;
        }
        $db->close();
        exit();
    }
}
