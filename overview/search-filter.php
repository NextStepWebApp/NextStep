<?php
session_start();
require_once "../utils.php";
setup_checker();
loginSecurity();
require_permission("view_students");

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

if (isset($_POST["submit"])) {
    if (
        empty(trim($_POST["student_name"])) &&
        empty(trim($_POST["student_email"])) &&
        empty(trim($_POST["student_phone"])) &&
        empty(trim($_POST["class_name"])) &&
        empty(trim($_POST["country_name"])) &&
        empty(trim($_POST["city_name"])) &&
        empty(trim($_POST["school_name"])) &&
        empty(trim($_POST["program_name"])) &&
        empty(trim($_POST["status"])) &&
        empty(trim($_POST["accessibility"])) &&
        empty(trim($_POST["date"]))
    ) {
        $_SESSION["error"] = "At least one field is required";
        header("Location: /NextStep/overview/search-filter.php");
        $db->close();
        exit();
    }

    $search = [];

    if (!empty(trim($_POST["student_name"]))) {
        $search["students_name"] = trim($_POST["student_name"]);
    }
    if (!empty(trim($_POST["student_email"]))) {
        $search["students_email"] = trim($_POST["student_email"]);
    }
    if (!empty(trim($_POST["student_phone"]))) {
        $search["students_phone_number"] = trim($_POST["student_phone"]);
    }
    if (!empty($_POST["class_name"])) {
        $search["class_name"] = $_POST["class_name"];
    }
    if (!empty($_POST["country_name"])) {
        $search["country_name"] = $_POST["country_name"];
    }
    if (!empty($_POST["city_name"])) {
        $search["city_name"] = $_POST["city_name"];
    }
    if (!empty($_POST["school_name"])) {
        $search["school_name"] = $_POST["school_name"];
    }
    if (!empty($_POST["program_name"])) {
        $search["program_name"] = $_POST["program_name"];
    }
    if (!empty($_POST["status"])) {
        $search["status_name"] = $_POST["status"];
    }
    if (!empty($_POST["accessibility"])) {
        $search["accessibility_name"] = $_POST["accessibility"];
    }
    if (!empty($_POST["date"])) {
        $search["students_created_date"] = $_POST["date"];
    }

    $_SESSION["search"] = $search;
    header("Location: /NextStep/overview/");
    $db->close();
    exit();
}

// The data options from the config files
$accessibility = $config["accessibility"];
$city = $config["city"];
$class = $config["class"];
$country = $config["country"];
$education = $config["education"];
$schools = $config["school"];
$status = $config["status"];

// Get all the created dates from the database
$query = "SELECT DISTINCT students_created_date FROM STUDENTS;";
$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing query $query", $db->lastErrorMsg());
}
$results = $stmt->execute();
if (!$results) {
    errorMessages("Error executing query", $db->lastErrorMsg());
}
$dates = [];
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $dates[] = $row["students_created_date"];
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
<title>NextStep - Search & Filter</title>
</head>
<body class="theme-<?= $color_theme ?>">
<?php include "../navbar.php"; ?>
<div class="page-box-wide">
<h2>Search & Filter Alumni</h2>
<?php flashMessages(); ?>

<form method="POST" action="search-filter.php">
    <label for="student_name">Name:</label>
    <input type="text" id="student_name" name="student_name"/>

    <label for="student_email">Email:</label>
    <input type="email" id="student_email" name="student_email"/>

    <label for="student_phone">Phone number:</label>
    <input type="tel" id="student_phone" name="student_phone"/>

    <label for="class_name">Class name:</label>
    <select id="class_name" name="class_name">
        <option value="">Select Class</option>
        <?php foreach ($class as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>">
                <?= htmlspecialchars($c) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="country_name">Country:</label>
    <select id="country_name" name="country_name">
        <option value="">Select Country</option>
        <?php foreach ($country as $cnt): ?>
            <option value="<?= htmlspecialchars($cnt) ?>">
                <?= htmlspecialchars($cnt) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="city_name">City:</label>
    <select id="city_name" name="city_name">
        <option value="">Select City</option>
        <?php foreach ($city as $cty): ?>
            <option value="<?= htmlspecialchars($cty) ?>">
                <?= htmlspecialchars($cty) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="school_name">School:</label>
    <select id="school_name" name="school_name">
        <option value="">Select School</option>
        <?php foreach ($schools as $school): ?>
            <option value="<?= htmlspecialchars($school) ?>">
                <?= htmlspecialchars($school) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="program_name">Program:</label>
    <select id="program_name" name="program_name">
        <option value="">Select Program</option>
        <?php foreach ($education as $edu): ?>
            <option value="<?= htmlspecialchars($edu) ?>">
                <?= htmlspecialchars($edu) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="status">Status:</label>
    <select id="status" name="status">
        <option value="">Select Status</option>
        <?php foreach ($status as $stat): ?>
            <option value="<?= htmlspecialchars($stat) ?>">
                <?= htmlspecialchars($stat) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="accessibility">Accessibility:</label>
    <select id="accessibility" name="accessibility">
        <option value="">Select</option>
        <?php foreach ($accessibility as $acc): ?>
            <option value="<?= htmlspecialchars($acc) ?>">
                <?= htmlspecialchars($acc) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="date">Date:</label>
      <select id="date" name="date">
          <option value="">Select</option>
          <?php foreach ($dates as $date): ?>
              <option value="<?= htmlspecialchars($date) ?>">
                  <?= htmlspecialchars($date) ?>
              </option>
          <?php endforeach; ?>

      </select>


    <div class="button-container">
        <input type="submit" class="simple-btn" name="submit" value="Search Alumni">
        <a href="/NextStep/overview/" class="simple-btn cancel-btn">Cancel</a>
    </div>
</form>
</div>
<script src="../js/script.js"></script>
</body>
</html>
