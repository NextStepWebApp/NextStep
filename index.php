<?php
require_once "utils.php";
session_start();
loginSecurity();

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}
$query = "SELECT
    STUDENTS.students_id,
    STUDENTS.students_name,
    STUDENTS.students_email,
    STATUS.status_name,
    STUDENTS.students_created_date
FROM STUDENTS
LEFT JOIN STATUS ON STUDENTS.students_status_id = STATUS.status_id;";

$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing query", $db->lastErrorMsg());
}
$results = $stmt->execute();
if (!$results) {
    errorMessages("Error executing query", $db->lastErrorMsg());
}

$query = "SELECT count(*) AS COUNT FROM STUDENTS";
$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing query", $db->lastErrorMsg());
}
$resultcount = $stmt->execute();
if (!$resultcount) {
    errorMessages("Error executing query", $db->lastErrorMsg());
}
$row = $resultcount->fetchArray();
$totalCount = $row['COUNT'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="css/style_index.css"/>
<link rel="stylesheet" href="css/style_navbar.css"/>
<title>NextStep</title>
</head>
<body>
<?php include 'navbar.php'; ?>
<main>
<section class="action-buttons">
<button type="button" class="action-btn" id="searchBtn">🔍 Search & Filter</button>
<span class="workflow-indicator">→</span>
<button type="button" class="action-btn" id="composeBtn" disabled>
✉️ Compose Email (<span id="selectedCount">0</span> selected)</button>
</section>

<section class="table-section">
<header class="table-header">

<?php
echo '<div class="selected-info">'.$totalCount.' records | 0 selected</div>';
?>

<div class="select-all-container">
<input type="checkbox" id="selectAll"/>
<label for="selectAll">Select All</label>
</div>
</header>
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
</section>
</main>
</body>
</html>
