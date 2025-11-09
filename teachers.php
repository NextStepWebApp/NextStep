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
<a href="create_teacher.php" class="nav-btn">Create teacher</a>
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
    
    echo '<tr>
            <td>' . $name . '</td>
            <td>' . $username . '</td>
            <td>' . $email . '</td>
            <td>
                <a href="edit_teacher.php?teacher_id=' . $id . '" class="nav-btn">Edit</a>';
                
    if ($username != "ADMIN") {
        echo '<a href="delete.php?teacher_id=' . $id . '" class="nav-btn">Delete</a>';
    }
    if(isset($_SESSION["new_teacher_credentials"])) {
        echo '<a href="teacher_download.php" class="nav-btn">Download</a>';
    }
    
    echo '    </td>
        </tr>';
}
$db->close();
?>
</tbody>
</table>
</div>
</section>
</body>
</html>
