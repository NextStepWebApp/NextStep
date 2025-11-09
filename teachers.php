<?php
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);

$db = new SQLite3($db_file);



?>
<!DOCTYPE html>
<html lang="en">
<head>
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="css/style_navbar.css"/>
<title>NextStep - Settings</title>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <?php flashMessages();?>
    <div class="table-container">
    <table>
    <thead>
    <tr>
    <th>Select</th>
    <th>Name</th>
    <th>Email</th>
    <th>Status</th>
    <th>Date</th>
    </tr>
    </thead>
    <tbody id="tableBody">
    <?php
    $hasresults = 0;
    while ($row = $results->fetchArray()) {
        $hasresults = 1;
        $student_id = htmlspecialchars($row["students_id"]);
        $date = htmlspecialchars($row["students_created_date"]);
        $name = htmlspecialchars($row["students_name"]);
        $email = htmlspecialchars($row["students_email"]);
        $status = htmlspecialchars($row["status_name"]);
        echo '<tr>
            <td><input type="checkbox" class="row-checkbox"/></td>
            <td><a href=view.php?student_id='.$student_id.'>'.$name.'</a></td>
            <td>'.$email.'</td>
            <td>'.$status.'</td>
            <td>'.$date.'</td>
            </tr>'."\n";
    }
    if ($hasresults == 0)
        echo('<tr><td colspan="5" class="no-records">No records found</td></tr>');
    $db->close();
    ?>
    </tbody>
    </table>
    </div>

</body>
</html>
