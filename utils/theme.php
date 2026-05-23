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
