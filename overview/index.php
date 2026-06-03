<?php
session_start();
require_once "../utils.php";
require_once "../mailer.php";
setup_checker();
loginSecurity();
require_permission("view_students");

if (isset($_GET["clear"])) {
    unset($_SESSION["search"]);
    header("Location: /NextStep/overview/");
    exit();
}

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

$has_search = false;
$totalCount = 0;
$rows       = [];

if (!empty($_SESSION["search"])) {
    $has_search = true;
    $search     = $_SESSION["search"];
    $totalCount = query_students($db, $search, "count");   
    $rows       = query_students($db, $search);   
}

$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);
$db->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="../images/logo.webp"/>
<link rel="stylesheet" href="../css/style_navbar.css"/>
<link rel="stylesheet" href="../css/style_page.css"/>
<title>NextStep - Overview</title>
</head>
<body class="theme-<?= $color_theme ?>">
<?php include "../navbar.php"; ?>
<section class="table-section">
<?php flashMessages(); ?>

<div class="action-buttons">

    <?php if ($has_search): ?>
        <a href="/NextStep/overview/?clear=1" class="action-btn cancel-btn">✕ Clear Filters</a>
    <?php else: ?>
        <a href="search-filter.php" class="action-btn">Search & Filter</a>
    <?php endif; ?>

    <span class="workflow-indicator">→</span>
   
    <?php if ($has_search && $totalCount > 0): ?>
        <a href="/NextStep/email/" class="action-btn">
            Compose Email (<?= $totalCount ?> <?= $totalCount === 1 ? 'record' : 'records' ?>)
        </a>
    <?php else: ?>
        <a class="action-btn disabled-btn">Compose Email</a>
    <?php endif; ?>

</div>

<?php if ($has_search && !empty($_SESSION["search"])): ?>
<div class="active-filters">
    <span class="filters-label">Active filters:</span>
    <?php foreach ($_SESSION["search"] as $filter): ?>
        <span class="filter-tag"><?= htmlspecialchars($filter) ?></span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="table-container">
<table>
<thead>
<tr>
<th>Name</th>
<th>Email</th>
<th>Status</th>
<th>Date</th>
</tr>
</thead>
<tbody id="tableBody">
<?php if (!$has_search): ?>
    <tr>
        <td colspan="5" class="no-students">
            Use <strong>Search & Filter</strong> to find alumni.
        </td>
    </tr>
<?php elseif ($totalCount === 0): ?>
    <tr>
        <td colspan="5" class="no-students">
            No students found.
            <a href="/NextStep/students">Add Students</a>
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($rows as $row): ?>
        <?php
            $student_id = htmlspecialchars($row["students_id"]);
            $date       = htmlspecialchars($row["students_created_date"]);
            $name       = htmlspecialchars($row["students_name"]);
            $email      = htmlspecialchars($row["students_email"]);
            $status     = htmlspecialchars($row["status_name"]);
            $viewUrl    = "view.php?student_id=$student_id";
        ?>
        <tr id="student_<?= $student_id ?>">
        <td>
            <a href="<?= $viewUrl ?>" class="email-link">
            <?= $name ?>
            </a>
        </td>
            <td><?= $email ?></td>
            <td><?= $status ?></td>
            <td><?= $date ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</section>
<script src="../js/script.js"></script>
</body>
</html>