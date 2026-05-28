<?php
session_start();
require_once "../utils.php";
setup_checker();
loginSecurity();
require_permission("view_students");

if (isset($_GET["clear"])) {
    unset($_SESSION["search"]);
    unset($_SESSION["list"]);
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
$rows = [];
$queryString = "";

if (!empty($_SESSION["search"])) {
    $has_search = true;

    $search = $_SESSION["search"];

    $conditions = [];
    $params = [];

    $like_fields = [
        "students_name",
        "students_email",
        "students_phone_number",
        "students_created_date"
    ];

    $name_fields = [
        "class_name"         => ["table" => "CLASS",             "column" => "class_name"],
        "country_name"       => ["table" => "COUNTRY",           "column" => "country_name"],
        "city_name"          => ["table" => "CITY",              "column" => "city_name"],
        "school_name"        => ["table" => "SCHOOL",            "column" => "school_name"],
        "program_name"       => ["table" => "EDUCATION_PROGRAM", "column" => "program_name"],
        "status_name"        => ["table" => "STATUS",            "column" => "status_name"],
        "accessibility_name" => ["table" => "ACCESSIBILITY",     "column" => "accessibility_name"],
    ];

    foreach ($like_fields as $field) {
        if (!empty($search[$field])) {
            $placeholder = ":" . $field;
            $conditions[] = "STUDENTS.$field LIKE $placeholder";
            $params[$placeholder] = ["value" => "%" . $search[$field] . "%", "type" => SQLITE3_TEXT];
        }
    }

    foreach ($name_fields as $key => $info) {
        if (!empty($search[$key])) {
            $placeholder = ":" . $key;
            $conditions[] = "{$info['column']} = $placeholder";
            $params[$placeholder] = ["value" => $search[$key], "type" => SQLITE3_TEXT];
        }
    }

    $where_clause = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

    $joins = "JOIN STATUS ON STUDENTS.students_status_id = STATUS.status_id
              JOIN CLASS ON STUDENTS.students_class_id = CLASS.class_id
              JOIN COUNTRY ON STUDENTS.students_country_id = COUNTRY.country_id
              JOIN CITY ON STUDENTS.students_city_id = CITY.city_id
              JOIN SCHOOL ON STUDENTS.students_school_id = SCHOOL.school_id
              JOIN EDUCATION_PROGRAM ON STUDENTS.students_education_program_id = EDUCATION_PROGRAM.program_id
              JOIN ACCESSIBILITY ON STUDENTS.students_accessibility_id = ACCESSIBILITY.accessibility_id";

    $query = "SELECT COUNT(*) as total FROM STUDENTS $joins $where_clause;";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing count query", $db->lastErrorMsg());
    }
    foreach ($params as $key => $p) {
        $stmt->bindValue($key, $p["value"], $p["type"]);
    }
    $result = $stmt->execute();
    if ($result) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $totalCount = $row["total"] ?? 0;
    }

    $query = "SELECT students_id, students_name, students_email,
                students_created_date, status_name
              FROM STUDENTS $joins $where_clause
              ORDER BY STUDENTS.students_name ASC;";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing main query", $db->lastErrorMsg());
    }
    foreach ($params as $key => $p) {
        $stmt->bindValue($key, $p["value"], $p["type"]);
    }
    $result = $stmt->execute();
    if (!$result) {
        errorMessages("Error executing main query", $db->lastErrorMsg());
    }

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
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
<title>NextStep</title>
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
        <a href="/NextStep/overview/email.php" class="action-btn">
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
            if (!empty($queryString)) {
                $viewUrl .= "&" . htmlspecialchars($queryString);
            }
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