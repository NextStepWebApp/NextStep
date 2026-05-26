<?php

function color_theme_helper(SQLite3 $db, string $color_theme)
{
    $method = is_admin_checker($db);
    if ($method == "json") {
        return $color_theme;
    } elseif ($method == "sql") {
        $query = "SELECT THEME.theme_name FROM THEME
        JOIN TEACHERS ON THEME.theme_id = TEACHERS.teacher_theme_id
        WHERE TEACHERS.teacher_id = :teacher_id";
        $stmt = $db->prepare($query);
        $stmt->bindValue(
            ":teacher_id",
            $_SESSION["teacher_id"],
            SQLITE3_INTEGER,
        );
        $result = $stmt->execute();
        $row = $result->fetchArray();

        # The teacher does not yet have a own color selected (new teacher)
        if (!$row) {
            return $color_theme;
        }

        $teacher_color = $row[0];
        return $teacher_color;
    }
}

// Fucntion used for the color theme helper
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
