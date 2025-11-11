<?php
require_once "utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);

$db = new SQLite3($db_file);

# Fetch all teachers
$query = "SELECT teacher_id, teacher_name, teacher_username, teacher_email FROM TEACHERS";
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
<link rel="stylesheet" href="css/style_page.css"/>
<link rel="stylesheet" href="css/style_navbar.css"/>
<title>NextStep - Teachers</title>
</head>
<body>
<?php include 'navbar.php'; ?>
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
<th>Actions</th>
</tr>
</thead>
<tbody id="tableBody">
    <?php
    while ($row = $results->fetchArray()) {
        $name = htmlspecialchars($row["teacher_name"]);
        $username = htmlspecialchars($row["teacher_username"]);
        $email = htmlspecialchars($row["teacher_email"]);
        $id = $row["teacher_id"];
    ?>
        <tr>
            <td><?= $name ?></td>
            <td><?= $username ?></td>
            <td><?= $email ?></td>
            <td>
              <button class="simple-btn" data-open-modal>Actions</button>
              <dialog data-modal>
                  <h2>Teacher Actions</h2>
                    <a href="edit_teacher.php?teacher_id=<?= $id ?>" class="simple-btn">Edit</a>
                    <?php if ($username != "ADMIN"): ?>
                      <a href="delete_teacher.php?teacher_id=<?= $id ?>" class="simple-btn">Delete</a>
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
<script src="js/script.js"></script>
</body>
</html>
