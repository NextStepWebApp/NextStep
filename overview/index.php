<?php
session_start();
require_once "../utils.php";
setup_checker();
loginSecurity();
require_permission("view_students");

// --- Clear search session ---
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

    $id_fields = [
        "students_class_id",
        "students_country_id",
        "students_city_id",
        "students_school_id",
        "students_education_program_id",
        "students_status_id",
        "students_accessibility_id"
    ];

    foreach ($like_fields as $field) {
        if (!empty($search[$field])) {
            $placeholder = ":" . $field;
            $conditions[] = "s.$field LIKE $placeholder";
            $params[$placeholder] = ["value" => "%" . $search[$field] . "%", "type" => SQLITE3_TEXT];
        }
    }

    foreach ($id_fields as $field) {
        if (!empty($search[$field])) {
            $placeholder = ":" . $field;
            $conditions[] = "s.$field = $placeholder";
            $params[$placeholder] = ["value" => (int)$search[$field], "type" => SQLITE3_INTEGER];
        }
    }

    $where_clause = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

    // Count query
    $count_query = "SELECT COUNT(*) as total
                    FROM STUDENTS s
                    LEFT JOIN STATUS st ON s.students_status_id = st.status_id
                    $where_clause;";

    $stmt = $db->prepare($count_query);
    if (!$stmt) {
        errorMessages("Error preparing count query", $db->lastErrorMsg());
    }
    foreach ($params as $key => $p) {
        $stmt->bindValue($key, $p["value"], $p["type"]);
    }
    $count_result = $stmt->execute();
    if ($count_result) {
        $count_row = $count_result->fetchArray(SQLITE3_ASSOC);
        $totalCount = $count_row["total"] ?? 0;
    }

    // Main query
    $main_query = "SELECT s.students_id, s.students_name, s.students_email,
                          s.students_created_date, st.status_name
                   FROM STUDENTS s
                   LEFT JOIN STATUS st ON s.students_status_id = st.status_id
                   $where_clause
                   ORDER BY s.students_name ASC;";

    $stmt2 = $db->prepare($main_query);
    if (!$stmt2) {
        errorMessages("Error preparing main query", $db->lastErrorMsg());
    }
    foreach ($params as $key => $p) {
        $stmt2->bindValue($key, $p["value"], $p["type"]);
    }
    $raw = $stmt2->execute();
    if (!$raw) {
        errorMessages("Error executing main query", $db->lastErrorMsg());
    }

    // Collect all rows into a plain array BEFORE closing the DB
    while ($row = $raw->fetchArray(SQLITE3_ASSOC)) {
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
    <div class="selected-info">
        <?= $totalCount ?> records | 0 selected
    </div>

    <?php if ($has_search): ?>
        <a href="/NextStep/overview/?clear=1" class="action-btn cancel-btn">✕ Clear Filters</a>
    <?php else: ?>
        <a href="search-filter.php" class="action-btn">Search & Filter</a>
    <?php endif; ?>

    <span class="workflow-indicator">→</span>
    <button type="button" class="action-btn" id="composeBtn" disabled>
        Compose Email (<span id="selectedCount">0</span> selected)
    </button>
    <div class="select-all-container">
        <input type="checkbox" id="selectAll"/>
        <label for="selectAll">Select All</label>
    </div>
</div>

<?php if ($has_search && !empty($_SESSION["list"])): ?>
<div class="active-filters">
    <span class="filters-label">Active filters:</span>
    <?php foreach ($_SESSION["list"] as $filter): ?>
        <span class="filter-tag"><?= htmlspecialchars($filter) ?></span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

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
            <td><input type="checkbox" class="check-box"></td>
            <td><a href="<?= $viewUrl ?>"><?= $name ?></a></td>
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