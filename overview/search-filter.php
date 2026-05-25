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

if (isset($_GET["submit"])) {
    if (
        empty($_GET["student_name"]) &&
        empty($_GET["student_email"]) &&
        empty($_GET["student_phone"]) &&
        empty($_GET["class_name"]) &&
        empty($_GET["country_name"]) &&
        empty($_GET["city_name"]) &&
        empty($_GET["school_name"]) &&
        empty($_GET["program_name"]) &&
        empty($_GET["status"]) &&
        empty($_GET["accessibility"]) &&
        empty($_GET["date"])
    ) {
        $_SESSION["error"] = "Atleast one field is required";
        header("Location: /NextStep/search-filter.php");
        $db->close();
        exit();
    }

    $query_params = [];

    // These are unique and are in one table
    if (!empty($_GET["student_name"])) {
        $query_params[] = "name=" . urlencode($_GET["student_name"]);
    }
    if (!empty($_GET["student_email"])) {
        $query_params[] = "email=" . urlencode($_GET["student_email"]);
    }
    if (!empty($_GET["student_phone"])) {
        $query_params[] = "phone=" . urlencode($_GET["student_phone"]);
    }

    // These are in other tables, so need to get the foreign key

    if (!empty($_GET["class_name"])) {
        $class_id = getForeignKey(
            $db,
            "SELECT class_id FROM CLASS WHERE class_name = :class_name",
            ":class_name",
            $_GET["class_name"],
            SQLITE3_TEXT,
        );

        if ($class_id !== null) {
            $query_params[] = "class=" . urlencode($class_id);
        }
    }

    if (!empty($_GET["country_name"])) {
        $country_id = getForeignKey(
            $db,
            "SELECT country_id FROM COUNTRY WHERE country_name = :country_name",
            ":country_name",
            $_GET["country_name"],
            SQLITE3_TEXT,
        );

        if ($country_id !== null) {
            $query_params[] = "country=" . urlencode($country_id);
        }
    }

    if (!empty($_GET["city_name"])) {
        $city_id = getForeignKey(
            $db,
            "SELECT city_id FROM CITY WHERE city_name = :city_name",
            ":city_name",
            $_GET["city_name"],
            SQLITE3_TEXT,
        );

        if ($city_id !== null) {
            $query_params[] = "city=" . urlencode($city_id);
        }
    }

    if (!empty($_GET["school_name"])) {
        $school_id = getForeignKey(
            $db,
            "SELECT school_id FROM SCHOOL WHERE school_name = :school_name",
            ":school_name",
            $_GET["school_name"],
            SQLITE3_TEXT,
        );

        if ($school_id !== null) {
            $query_params[] = "school=" . urlencode($school_id);
        }
    }
    if (!empty($_GET["program_name"])) {
        $program_id = getForeignKey(
            $db,
            "SELECT program_id FROM EDUCATION_PROGRAM WHERE program_name = :program_name",
            ":program_name",
            $_GET["program_name"],
            SQLITE3_TEXT,
        );

        if ($program_id !== null) {
            $query_params[] = "program=" . urlencode($program_id);
        }
    }

    if (!empty($_GET["status"])) {
        $status_id = getForeignKey(
            $db,
            "SELECT status_id FROM STATUS WHERE status_name = :status_name",
            ":status_name",
            $_GET["status"],
            SQLITE3_TEXT,
        );

        if ($status_id !== null) {
            $query_params[] = "status=" . urlencode($status_id);
        }
    }

    if (!empty($_GET["accessibility"])) {
        $accessibility_id = getForeignKey(
            $db,
            "SELECT accessibility_id FROM ACCESSIBILITY WHERE accessibility_name = :accessibility_name",
            ":accessibility_name",
            $_GET["accessibility"],
            SQLITE3_TEXT,
        );

        if ($accessibility_id !== null) {
            $query_params[] = "accessibility=" . urlencode($accessibility_id);
        }
    }

    if (!empty($_GET["date"])) {
        $query_params[] = "date=" . urlencode($_GET["date"]);
    }

    $query_string = implode("&", $query_params);
    header("Location: /NextStep/overview/?" . $query_string);
    exit();
}

$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);

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

$db->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="../css/style_navbar.css"/>
<link rel="stylesheet" href="../css/style_page.css"/>
<title>NextStep - Search & Filter</title>
</head>
<body class="theme-<?= $color_theme ?>">
<?php include "../navbar.php"; ?>
<div class="page-box-wide">
<h2>Search & Filter Alumni</h2>
<?php flashMessages(); ?>

<form method="GET" action="search-filter.php">
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
          <
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
