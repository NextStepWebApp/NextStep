<?php
require_once "../utils.php";
session_start();
loginSecurity();
require_permission("teachers_access");

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

# handle post from roles
if (isset($_POST["submit_role"])) {
    if (
        isset($_POST["role"]) &&
        isset($_POST["teacher_id"]) &&
        isset($_POST["teacher_username"])
    ) {
        $new_role_id = intval($_POST["role"]);
        $teacher_id = intval($_POST["teacher_id"]);
        $teacher_username = $_POST["teacher_username"];

        # Validate that the role exists
        $query = "SELECT role_name FROM ROLES WHERE role_id = :role_id";
        $stmt = $db->prepare($query);
        if (!$stmt) {
            errorMessages("Error preparing query", $db->lastErrorMsg());
        }
        $stmt->bindValue(":role_id", $new_role_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        if (!$result) {
            errorMessages("Error executing query", $db->lastErrorMsg());
        }
        $existing = $result->fetchArray(SQLITE3_ASSOC);
        if (!$existing) {
            $_SESSION["error"] = "Invalid role selected";
            header("Location: /NextStep/teachers");
            $db->close();
            exit();
        } else {
            # Check to see if username is "ADMIN" 
            if ($teacher_username == "ADMIN") {
                $_SESSION["error"] = "The original ADMIN can not change roles";
                header("Location: /NextStep/teachers");
                $db->close();
                exit();
            }

            // Check to prevent other users to get admin power
            $role_name = $existing["role_name"]; 
            if ($role_name == "ADMIN") {
                $_SESSION["error"] = "There is only one ADMIN!";
                header("Location: /NextStep/teachers");
                $db->close();
                exit();
            }

            #Update the teacher's role
            $query =
                "UPDATE TEACHERS SET teacher_role_id = :role_id WHERE teacher_id = :teacher_id";
            $stmt = $db->prepare($query);
            if (!$stmt) {
                errorMessages("Error preparing query", $db->lastErrorMsg());
            }
            $stmt->bindValue(":role_id", $new_role_id, SQLITE3_INTEGER);
            $stmt->bindValue(":teacher_id", $teacher_id, SQLITE3_INTEGER);
            $update = $stmt->execute();
            if (!$update) {
                errorMessages("Error updating role", $db->lastErrorMsg());
            }

            $_SESSION["success"] = "Teacher $teacher_username role updated successfully";
            header("Location: /NextStep/teachers");
            $db->close();
            exit();
        }
    }
}

# Get help from the theme helper
$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);

# Fetch all teachers
$query = "SELECT TEACHERS.teacher_id, TEACHERS.teacher_name, TEACHERS.teacher_username, SMTP.smtp_email, ROLES.role_name
FROM TEACHERS
JOIN ROLES ON TEACHERS.teacher_role_id = ROLES.role_id
LEFT JOIN SMTP ON TEACHERS.teacher_id = SMTP.teacher_id;";

$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing query", $db->lastErrorMsg());
}
$results_teachers = $stmt->execute();
if (!$results_teachers) {
    errorMessages("Error executing query", $db->lastErrorMsg());
}

# Get the roles from the db
$query = "SELECT ROLES.role_id, ROLES.role_name FROM ROLES";
$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing query", $db->lastErrorMsg());
}
$results_roles = $stmt->execute();
if (!$results_roles) {
    errorMessages("Error executing query", $db->lastErrorMsg());
}

# Fetch ALL roles into an array
$row_roles = [];
while ($role = $results_roles->fetchArray(SQLITE3_ASSOC)) {
    if ($role["role_name"] == "ADMIN") {
        continue;
    }
    $row_roles[] = $role;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="../images/logo.webp"/>
<link rel="stylesheet" href="../css/style_page.css"/>
<link rel="stylesheet" href="../css/style_navbar.css"/>
<title>NextStep - Teachers</title>
</head>
<body class="theme-<?= $color_theme ?>">

<?php include "../navbar.php"; ?>
<section class="table-section">
<?php flashMessages(); ?>
<div class="teacher-button">
<a href="create_teacher.php" class="simple-btn">Create teacher</a>
</div>
<div class="table-container">
<table>
<thead>
<tr>
<th>Name</th>
<th>Username</th>
<th>Email</th>
<th>Role</th>
<th>Actions</th>
</tr>
</thead>
<tbody id="tableBody">
    <?php
    while ($row = $results_teachers->fetchArray()) {

        $name = htmlspecialchars($row["teacher_name"]);
        $username = htmlspecialchars($row["teacher_username"]);
        $email = htmlspecialchars($row["smtp_email"] ?? "No email settings set up");
        $role = htmlspecialchars($row["role_name"]);
        $id = $row["teacher_id"];
        ?>
        <tr>
            <td><?= $name ?></td>
            <td><?= $username ?></td>
            <td>
                <?php if (has_permission("system_smtp", $id, $role)): ?>
                    <a href="/NextStep/teachers/view.php?teacher_id=<?= $id ?>" class="email-link">
                    <?= $email ?>
                    </a>
                <?php else: ?>
                    <?= "No permission" ?>
                <?php endif; ?> 
            </td> 
            <td><?= $role ?></td>
            <td>
            <button class="simple-btn" data-open-modal>Actions</button>
            <dialog data-modal>
                <h2>Teacher Actions</h2>
                <a href="/NextStep/teachers/edit_teacher.php?teacher_id=<?= $id ?>" class="simple-btn">Edit</a>
                <?php if ($username != "ADMIN"): ?>
                    <button class="simple-btn" data-open-modal>Delete</button>
                    <dialog data-modal>
                        <h2>Are you sure?</h2>
                        <a href="/NextStep/teachers/delete_teacher.php?teacher_id=<?= $id ?>" class="simple-btn">Confirm Delete</a>
                        <button class="simple-btn" data-close-modal>Cancel</button>
                    </dialog>
                      <button class="simple-btn" data-open-modal>Role</button>
                          <dialog data-modal>
                          <h2>Change Role</h2>
                          <form method="POST">
                              <input type="hidden" name="teacher_id" value="<?= $id ?>">
                              <input type="hidden" name="teacher_username" value="<?= $username ?>">
                              <select name="role">
                                  <?php foreach ($row_roles as $role_option) {
                                      $selected =
                                          $role_option["role_name"] == $role
                                              ? "selected"
                                              : "";
                                      echo "<option value='{$role_option["role_id"]}' $selected>{$role_option["role_name"]}</option>";
                                  } ?>
                              </select>
                              <div class="button-container">
                                  <input type="submit" class="simple-btn" name="submit_role" value="Update Role">
                                  <button class="simple-btn" data-close-modal>Cancel</button>
                              </div>
                          </form>
                          </dialog>
                <?php endif; ?>
            <button class="simple-btn" data-close-modal>Close</button>
            </dialog>
            </td>
        </tr>
    <?php
    }
    $db->close();
    ?>
</tbody>
</table>
</div>
</section>
<script src="../js/script.js"></script>
</body>
</html>
