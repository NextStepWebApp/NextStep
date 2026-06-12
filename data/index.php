<?php
require_once "../utils.php";
setup_checker();
session_start();
loginSecurity();
require_permission("data_overview");

try {
    $db = new SQLite3($db_file);
    $db->busyTimeout(10000);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}


// Get all the creation years from the db
$query = "SELECT DISTINCT students_created_date FROM STUDENTS 
        ORDER BY students_created_date DESC";
$result = $db->query($query);

$years = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $years[] = $row["students_created_date"];
}

$selected_year = (int) ($_GET["year"] ?? $years[0]);


// Get the card information
$queries = [
    "total" => "SELECT COUNT(*) FROM STUDENTS WHERE students_created_date = :year",
    "with_linkedin" => "SELECT COUNT(*) FROM STUDENTS WHERE students_created_date = :year AND students_linkedin_url IS NOT NULL AND students_linkedin_url != ''",
    "with_website" => "SELECT COUNT(*) FROM STUDENTS WHERE students_created_date = :year AND students_website IS NOT NULL AND students_website != ''",
    "with_bio" => "SELECT COUNT(*) FROM STUDENTS WHERE students_created_date = :year AND students_bio IS NOT NULL AND students_bio != ''",
    "with_job_title" => "SELECT COUNT(*) FROM STUDENTS WHERE students_created_date = :year AND students_job_title IS NOT NULL AND students_job_title != ''",
    "with_company" => "SELECT COUNT(*) FROM STUDENTS WHERE students_created_date = :year AND students_company IS NOT NULL AND students_company != ''",
];

$stats = [];
foreach ($queries as $key => $sql) {
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        errorMessages("Error preparing query", $db->lastErrorMsg());
    }
    $stmt->bindValue(":year", $selected_year, SQLITE3_INTEGER);
    $result = $stmt->execute();
    //$row = $result->fetchArray(SQLITE3_NUM);
    //$stats[$key] = $row ? $row[0] : 0;
    $stats[$key] = $result->fetchArray(SQLITE3_NUM)[0];
}

// Get the top countries
$query = "SELECT COUNTRY.country_name, COUNT(*) as count
        FROM STUDENTS
        JOIN COUNTRY ON STUDENTS.students_country_id = COUNTRY.country_id
        WHERE students_created_date = :year 
        GROUP BY COUNTRY.country_name
        ORDER BY count DESC";

$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing query", $db->lastErrorMsg());
}

$stmt->bindValue(":year", $selected_year, SQLITE3_INTEGER);
$result = $stmt->execute();

$countries = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $countries[] = $row;
}

// Get the top programs
$query = "SELECT EDUCATION_PROGRAM.program_name, COUNT(*) as count
        FROM STUDENTS
        JOIN EDUCATION_PROGRAM ON STUDENTS.students_education_program_id = EDUCATION_PROGRAM.program_id
        WHERE students_created_date = :year
        GROUP BY EDUCATION_PROGRAM.program_name
        ORDER BY count DESC";

$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing query", $db->lastErrorMsg());
}

$stmt->bindValue(":year", $selected_year, SQLITE3_INTEGER);
$result = $stmt->execute();

$programs = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $programs[] = $row;
}

// Get the top schools
$query = "SELECT SCHOOL.school_name, COUNT(*) as count
        FROM STUDENTS
        JOIN SCHOOL ON STUDENTS.students_school_id = SCHOOL.school_id
        WHERE students_created_date = :year
        GROUP BY SCHOOL.school_name
        ORDER BY count DESC";

$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing query", $db->lastErrorMsg());
}

$stmt->bindValue(":year", $selected_year, SQLITE3_INTEGER);
$result = $stmt->execute();

$schools = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $schools[] = $row;
}



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
<title>NextStep — Data & Insights</title>
<style>
</style>
</head>
<body class="theme-<?= $color_theme ?>">
<?php include "../navbar.php"; ?>
<?php flashMessages(); ?>

<div class="insights-wrapper">

    <?php if ($stats["total"] == 0): ?>

        <div class="empty-state">
            <p>There are no alumni records for this graduating year yet.</p>
        </div>

    <?php else: ?>
    <div class="year-bar">
        <select id="year-select" onchange="window.location.href='?year='+this.value">
            <?php foreach ($years as $year): ?>
                <option value="<?= htmlspecialchars($year) ?>" <?= $year == $selected_year ? "selected" : "" ?>>
                    <?= htmlspecialchars($year) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $stats["total"] ?></div>
            <div class="stat-label">Total Alumni</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats["with_linkedin"] ?></div>
            <div class="stat-label">LinkedIn Profiles</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats["with_website"] ?></div>
            <div class="stat-label">Personal Websites</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats["with_bio"] ?></div>
            <div class="stat-label">With Bio</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats["with_job_title"] ?></div>
            <div class="stat-label">With Job Title</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats["with_company"] ?></div>
            <div class="stat-label">With Company</div>
        </div>
    </div>

    <!-- Distribution Insights -->

    <div class="insights-grid">
        <?php if (!empty($countries)): ?>
        <div class="insight-card">
        <h3>Top Countries</h3>
        <ul class="insight-list">
            <?php foreach ($countries as $index => $country): ?>
            <li>
                <span class="rank">#<?= $index + 1 ?></span>
                <span class="name"><?= htmlspecialchars($country["country_name"]) ?></span>
                <span class="count"><?= $country["count"] ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($programs)): ?>
        <div class="insight-card">
            <h3>Top Programs</h3>
            <ul class="insight-list">
                <?php foreach ($programs as $index => $program): ?>
                <li>
                    <span class="rank">#<?= $index + 1 ?></span>
                    <span class="name"><?= htmlspecialchars($program["program_name"]) ?></span>
                    <span class="count"><?= $program["count"] ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($schools)): ?>
        <div class="insight-card">
            <h3>University Rank</h3>
            <ul class="insight-list">
                <?php foreach ($schools as $index => $school): ?>
                <li>
                    <span class="rank">#<?= $index + 1 ?></span>
                    <span class="name"><?= htmlspecialchars($school["school_name"]) ?></span>
                    <span class="count"><?= $school["count"] ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <?php endif; ?>

</div>

<script src="../js/script.js"></script>
</body>
</html>