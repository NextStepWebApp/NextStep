<?php
require_once "../utils.php";
session_start();
loginSecurity();
check_id($_GET["student_id"], "Students");
require_permission("view_students");

try {
    $db = new SQLite3($db_file);
    $db->busyTimeout(10000);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

$row = full_students_database_query($db);

if (!$row) {
    $_SESSION["error"] = "Invalid value for student_id";
    header("Location: index.php");
    exit();
}
$student_id = htmlspecialchars($row["students_id"]);
$student_name = htmlspecialchars($row["students_name"]);
$student_email = htmlspecialchars($row["students_email"]);
$student_company = htmlspecialchars($row["students_company"] ?? "");
$student_job_title = htmlspecialchars($row["students_job_title"] ?? "");
$student_linkedin_url = htmlspecialchars($row["students_linkedin_url"] ?? "");
$student_website = htmlspecialchars($row["students_website"] ?? "");
$student_bio = htmlspecialchars($row["students_bio"] ?? "");
$class_name = htmlspecialchars($row["class_name"]);
$country_name = htmlspecialchars($row["country_name"]);
$city_name = htmlspecialchars($row["city_name"]);
$school_name = htmlspecialchars($row["school_name"]);
$program_name = htmlspecialchars($row["program_name"]);
$status = htmlspecialchars($row["status_name"]);
$accessibility = htmlspecialchars($row["accessibility_name"]);
$created_date = htmlspecialchars($row["students_created_date"]);
$last_update = htmlspecialchars($row["students_last_updated"]);
$readable_date = $last_update;

# Get help from the theme helper
$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);

$db->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="../images/logo.webp"/>
<link rel="stylesheet" href="../css/style_navbar.css"/>
<link rel="stylesheet" href="../css/style_page.css"/>
<title>NextStep - View</title>
</head>
<body class="theme-<?= $color_theme ?>">
<?php include "../navbar.php"; ?>

<div class="page-box">
<h2>Student information</h2>
<?php flashMessages(); ?>
<p>Name: <strong><?= $student_name ?></strong></p>
<p>Email: <strong><?= $student_email ?></strong></p>

<?php if (!empty($student_company)): ?>
<p>Company: <strong><?= $student_company ?></strong></p>
<?php endif; ?>

<?php if (!empty($student_job_title)): ?>
<p>Job Title: <strong><?= $student_job_title ?></strong></p>
<?php endif; ?>

<?php if (!empty($student_linkedin_url)): ?>
<p>LinkedIn: <strong><a href="<?= $student_linkedin_url ?>" target="_blank"><?= $student_linkedin_url ?></a></strong></p>
<?php endif; ?>

<?php if (!empty($student_website)): ?>
<p>Website: <strong><a href="<?= $student_website ?>" target="_blank"><?= $student_website ?></a></strong></p>
<?php endif; ?>

<?php if (!empty($student_bio)): ?>
<p>Bio: <strong><?= $student_bio ?></strong></p>
<?php endif; ?>

<p>Class name: <strong><?= $class_name ?></strong></p>
<p>Country: <strong><?= $country_name ?></strong></p>
<p>City: <strong><?= $city_name ?></strong></p>
<p>School: <strong><?= $school_name ?></strong></p>
<p>Program: <strong><?= $program_name ?></strong></p>
<p>Status: <strong><?= $status ?></strong></p>
<p>Accessibility: <strong><?= $accessibility ?></strong></p>
<p>Date created: <strong><?= $created_date ?></strong></p>
<p>Date last update: <strong><?= $readable_date ?></strong></p>

<?php if (has_permission("change_students", $_SESSION["teacher_id"])): ?>
    <a href="/NextStep/students/edit_student.php?student_id=<?php echo $student_id; ?>" class="simple-btn">Edit</a>
    <button class="simple-btn" data-open-modal>Delete</button>
    <dialog data-modal>
        <h2>Are you sure?</h2>
        <p>This action cannot be undone.</p>
        <a href="/NextStep/students/delete_student.php?student_id=<?php echo $student_id; ?>" class="simple-btn">Confirm Delete</a>
        <button class="simple-btn" data-close-modal>Cancel</button>
    </dialog>
<?php endif; ?>

<a href="/NextStep/overview/#student_<?php echo $student_id; ?>" class="simple-btn">Back</a>
</div>
<script src="../js/script.js"></script>
</body>
</html>