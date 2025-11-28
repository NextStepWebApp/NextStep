<?php
require_once "../utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}
# Fetch all teachers
$query = <<<EOF
SELECT TEACHERS.teacher_id, TEACHERS.teacher_name, TEACHERS.teacher_username, 
TEACHERS.teacher_email, ROLES.role_name
FROM TEACHERS 
JOIN ROLES ON TEACHERS.teacher_role_id = ROLES.role_id;
EOF;  

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
    $row_roles[] = $role;
}

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="../css/style_page.css"/>
<link rel="stylesheet" href="../css/style_navbar.css"/>
<title>NextStep - Teachers</title>
</head>
<body>
<?php include "../navbar.php"; ?>
<section class="table-section"> 
<?php flashMessages();?>
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
        $email = htmlspecialchars($row["teacher_email"]);
        $role = htmlspecialchars($row["role_name"]);
        $id = $row["teacher_id"];
    ?>
        <tr>
            <td><?= $name ?></td>
            <td><?= $username ?></td>
            <td><?= $email ?></td>
            <td><?= $role ?></td>
            <td>
            <button class="simple-btn" data-open-modal>Actions</button>
            <dialog data-modal>
                <h2>Teacher Actions</h2>
                <a href="/NextStep/teachers/edit_teacher.php?teacher_id=<?= $id ?>" class="simple-btn">Edit</a>
                <?php if ($username != "ADMIN"): ?>
                <a href="/NextStep/teachers/delete_teacher.php?teacher_id=<?= $id ?>" class="simple-btn">Delete</a>
                <?php endif; ?>
                          
                <button class="simple-btn" data-open-modal>Role</button>
                    <dialog data-modal>
                    <h2>Change Role</h2>
                    <select name="role">
                        <?php
                            foreach ($row_roles as $role) {
                                echo "<option value='{$role['role_id']}'>{$role['role_name']}</option>";
                            }
                        ?>
                    </select>
                    <div class="dialog-buttons">
                        <button class="simple-btn">Update Role</button>
                        <button class="simple-btn" data-close-modal>Cancel</button>
                    </div>
                    </dialog>
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
