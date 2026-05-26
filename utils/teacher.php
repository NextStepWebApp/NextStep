<?php

function validate_teacher_name(
    SQLite3 $db,
    string $teacher_name,
    string $page,
) {
    if (strlen($teacher_name) < 2) {
        $_SESSION["error"] = "Name must be at least 2 characters long";

        switch ($page) {
            case "settings":
                header("Location: /NextStep/settings/?tab=general");
                $db->close();
                exit();

            case "create_teacher":
                header("Location: create_teacher.php");
                $db->close();
                exit();

            default:
                header("Location: index.php");
                $db->close();
                exit();
        }
    }

    // Check maximum length
    if (strlen($teacher_name) > 50) {
        $_SESSION["error"] = "Name must not exceed 50 characters";

        switch ($page) {
            case "settings":
                header("Location: /NextStep/settings/?tab=general");
                $db->close();
                exit();

            case "create_teacher":
                header("Location: create_teacher.php");
                $db->close();
                exit();

            default:
                header("Location: index.php");
                $db->close();
                exit();
        }
    }

    // Check valid characters
    if (!preg_match("/^[a-zA-Z\s\-'\.]+$/u", $teacher_name)) {
        $_SESSION["error"] = "Name contains invalid characters";

        switch ($page) {
            case "settings":
                header("Location: /NextStep/settings/?tab=general");
                $db->close();
                exit();

            case "create_teacher":
                header("Location: create_teacher.php");
                $db->close();
                exit();

            default:
                header("Location: index.php");
                $db->close();
                exit();
        }
    }
}

function validate_teacher_email(
    SQLite3 $db,
    string $teacher_email,
    string $page,
    ?int $teacher_id = null,
) {
    // Check email format
    if (!filter_var($teacher_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"] = "Invalid email format";

        switch ($page) {
            case "settings":
                header("Location: /NextStep/settings/?tab=email");
                $db->close();
                exit();
            
            default:
                header("Location: index.php");
                $db->close();
                exit();
        }
    }

    // Check maximum length
    if (strlen($teacher_email) > 50) {
        $_SESSION["error"] = "Email must not exceed 50 characters";

        switch ($page) {
            case "settings":
                header("Location: /NextStep/settings/?tab=email");
                $db->close();
                exit();

            default:
                header("Location: index.php");
                $db->close();
                exit();
        }
    }
}


function validate_teacher_username(
    SQLite3 $db, 
    string $teacher_username, 
    string $page, 
    ?int $teacher_id = null
) {
    if (strlen($teacher_username) < 3) {
        $_SESSION["error"] = "Username must be at least 3 characters long";
        
        switch ($page) {
            case "create_teacher":
                header("Location: create_teacher.php");
                $db->close();
                exit();
            case "edit_teacher":
                header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
                $db->close();
                exit();
            default:
                header("Location: index.php");
                $db->close();
                exit();
        }
    }

    if (strlen($teacher_username) > 30) {
        $_SESSION["error"] = "Username must not exceed 30 characters";
        
        switch ($page) {
            case "create_teacher":
                header("Location: create_teacher.php");
                $db->close();
                exit();
            case "edit_teacher":
                header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
                $db->close();
                exit();
            default:
                header("Location: index.php");
                $db->close();
                exit();
        }
    }

    if (!preg_match("/^[a-zA-Z_\-\.]+$/", $teacher_username)) {
        $_SESSION["error"] = "Username can only contain letters, dots, hyphens and underscores";
        
        switch ($page) {
            case "create_teacher":
                header("Location: create_teacher.php");
                $db->close();
                exit();
            case "edit_teacher":
                header("Location: edit_teacher.php?teacher_id=" . $teacher_id);
                $db->close();
                exit();
            default:
                header("Location: index.php");
                $db->close();
                exit();
        }
    }
}