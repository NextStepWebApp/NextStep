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
$results = $stmt->execute();
if (!$results) {
    errorMessages("Error executing query", $db->lastErrorMsg());
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
    while ($row = $results->fetchArray()) {
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
                    <h3>Change Role</h3>
                    <p>Change role for <strong><?= $username ?></strong><br>
                        Current role: <strong><?= $role ?></strong></p>
                      
                    <label for="roleSelect-<?= $id ?>">Select New Role:</label>
                    <select id="roleSelect-<?= $id ?>">
                        <option value="admin">ADMIN - Full system access and user management</option>
                        <option value="user">USER - View-only access to student records</option>
                        <option value="superuser">SUPERUSER - Advanced access with data management</option>
                        <option value="sysadmin">SYSADMIN - System administration and configuration</option>
                    </select>
                      
                    <div class="warning-box">
                        <p><strong>⚠️ Warning:</strong> Changing roles will affect this user's permissions immediately.</p>
                    </div>
                      
                    <div class="button-container">
                        <button class="btn btn-primary" onclick="saveRole(<?= $id ?>)">Update Role</button>
                        <button class="btn" data-close-modal>Cancel</button>
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
