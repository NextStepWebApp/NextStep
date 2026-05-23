<?php
session_start();
require_once "../utils.php";
require_once "query-builder.php";
setup_checker();
loginSecurity();
require_permission("view_students");

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

// These are the get requests from search-filter.php
$search_params = [
    "name" => $_GET["name"] ?? "",
    "email" => $_GET["email"] ?? "",
    "phone" => $_GET["phone"] ?? "",
    "class" => $_GET["class"] ?? "",
    "country" => $_GET["country"] ?? "",
    "city" => $_GET["city"] ?? "",
    "school" => $_GET["school"] ?? "",
    "program" => $_GET["program"] ?? "",
    "status" => $_GET["status"] ?? "",
    "accessibility" => $_GET["accessibility"] ?? "",
    "date" => $_GET["date"] ?? "",
];

$has_search = false;
foreach ($search_params as $value) {
    if ($value !== "" && $value !== null) {
        $has_search = true;
        break;
    }
}

$totalCount = 0;
$results = null;

if ($has_search) {
    $queries = buildAlumniSearchQuery($db, $search_params);

    $stmt = $queries["data"];
    $stmt_count = $queries["count"];

    $results = $stmt->execute();
    $result_count = $stmt_count->execute();

    if (!$results || !$result_count) {
        errorMessages("Error executing query", $db->lastErrorMsg());
    }

    $row = $result_count->fetchArray(SQLITE3_ASSOC);
    $totalCount = (int) $row["COUNT"];
}

# Get help from the theme helper
$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);

$queryString = http_build_query($search_params);

#$db->close(); is used still in the html
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
    <?php echo '<div class="selected-info">' .
        $totalCount .
        " records | 0 selected</div>"; ?>

    <a href="search-filter.php" class="action-btn">Search & Filter</a>


    <span class="workflow-indicator">→</span>
    <button type="button" class="action-btn" id="composeBtn" disabled>
        Compose Email (<span id="selectedCount">0</span> selected)
    </button>
    <div class="select-all-container">
        <input type="checkbox" id="selectAll"/>
        <label for="selectAll">Select All</label>
    </div>
</div>
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
<?php if (!$has_search) {
    echo '<tr>
            <td colspan="5" class="no-students">
                Use <strong>Search & Filter</strong> to find alumni.
            </td>
          </tr>';
} elseif ($totalCount === 0) {
    echo '<tr>
            <td colspan="5" class="no-students">
                No students found.
                <a href="/NextStep/students">Add Students</a>
            </td>
          </tr>';
} else {
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $student_id = htmlspecialchars($row["students_id"]);
        $date = htmlspecialchars($row["students_created_date"]);
        $name = htmlspecialchars($row["students_name"]);
        $email = htmlspecialchars($row["students_email"]);
        $status = htmlspecialchars($row["status_name"]);

        $viewUrl = "view.php?student_id=$student_id";

        if (!empty($queryString)) {
            $viewUrl .= "&" . htmlspecialchars($queryString);
        }

        echo "<tr id='student_$student_id'>
                <td><input type='checkbox' class='check-box'></td>
                <td><a href='$viewUrl'>$name</a></td>
                <td>$email</td>
                <td>$status</td>
                <td>$date</td>
              </tr>";
    }
} ?>
</tbody>
</table>
</div>
</section>
<script src="../js/script.js"></script>
</body>
</html>
