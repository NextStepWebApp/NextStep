<?php
require_once "../utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);

check_id($_GET["student_id"], "Students");

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

# Get help from the theme helper
$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);

# The config is defined in utils
$accessibility = $config["accessibility"];
$city = $config["city"];
$class = $config["class"];
$country = $config["country"];
$education = $config["education"];
$schools = $config["school"];
$status = $config["status"];

if (isset($_POST["submit"])) {
    if (
        empty($_POST["student_name"]) ||
        empty($_POST["student_email"]) ||
        empty($_POST["student_phone"]) ||
        empty($_POST["class_name"]) ||
        empty($_POST["country_name"]) ||
        empty($_POST["city_name"]) ||
        empty($_POST["school_name"]) ||
        empty($_POST["program_name"]) ||
        empty($_POST["status"]) ||
        empty($_POST["accessibility"])
    ) {
        $_SESSION["error"] = "All fields are required";
        header(
            "Location: /NextStep/students/edit_student.php?student_id=" .
                $_POST["student_id"],
        );
        $db->close();
        exit();
    }

    # validate student name
    validate_student_name(
        $db,
        $_POST["student_name"],
        "edit_student",
        $_POST["student_id"],
    );

    # Validate email
    validate_student_email(
        $db,
        $_POST["student_email"],
        "edit_student",
        $_POST["student_id"],
    );

    # Validate phone number
    $clean_phone = validate_student_phone(
        $db,
        $_POST["student_phone"],
        "edit_student",
        $_POST["student_id"],
    );

    $_POST["student_phone"] = $clean_phone;

    # Check if another student has the same email, name, or phone (excluding current student)
    $query = "SELECT students_id FROM STUDENTS WHERE
        (students_email = :email OR
        students_name = :name OR
        students_phone_number = :phone)
        AND students_id != :current_id";
    $stmt = $db->prepare($query);

    if (!$stmt) {
        errorMessages(
            "Error preparing duplicate check query",
            $db->lastErrorMsg(),
        );
    }

    $stmt->bindValue(":email", $_POST["student_email"], SQLITE3_TEXT);
    $stmt->bindValue(":name", $_POST["student_name"], SQLITE3_TEXT);
    $stmt->bindValue(":phone", $_POST["student_phone"], SQLITE3_TEXT);
    $stmt->bindValue(":current_id", $_POST["student_id"], SQLITE3_INTEGER);
    $result = $stmt->execute();

    if (!$result) {
        errorMessages("Error executing duplicate check", $db->lastErrorMsg());
    }

    $existing = $result->fetchArray();

    if ($existing) {
        $_SESSION["error"] =
            "Another student with this name, email, or phone number already exists";
        header(
            "Location: /NextStep/students/edit_student.php?student_id=" .
                $_POST["student_id"],
        );
        $db->close();
        exit();
    }

    # Validate all dropdown values against config
    if (
        !in_array($_POST["class_name"], $class) ||
        !in_array($_POST["country_name"], $country) ||
        !in_array($_POST["city_name"], $city) ||
        !in_array($_POST["school_name"], $schools) ||
        !in_array($_POST["program_name"], $education) ||
        !in_array($_POST["status"], $status) ||
        !in_array($_POST["accessibility"], $accessibility)
    ) {
        $_SESSION["error"] = "Invalid selection detected";
        error_log("Config validation error - invalid dropdown value submitted");
        header(
            "Location: /NextStep/students/edit_student.php?student_id=" .
                $_POST["student_id"],
        );
        $db->close();
        exit();
    }

    # Get or create foreign keys
    $result_class_id = get_or_create_foreign_key(
        $db,
        "CLASS",
        "class_id",
        "class_name",
        $_POST["class_name"],
    );
    $result_country_id = get_or_create_foreign_key(
        $db,
        "COUNTRY",
        "country_id",
        "country_name",
        $_POST["country_name"],
    );
    $result_city_id = get_or_create_foreign_key(
        $db,
        "CITY",
        "city_id",
        "city_name",
        $_POST["city_name"],
    );
    $result_school_id = get_or_create_foreign_key(
        $db,
        "SCHOOL",
        "school_id",
        "school_name",
        $_POST["school_name"],
    );
    $result_program_id = get_or_create_foreign_key(
        $db,
        "EDUCATION_PROGRAM",
        "program_id",
        "program_name",
        $_POST["program_name"],
    );
    $result_status_id = get_or_create_foreign_key(
        $db,
        "STATUS",
        "status_id",
        "status_name",
        $_POST["status"],
    );
    $result_accessibility_id = get_or_create_foreign_key(
        $db,
        "ACCESSIBILITY",
        "accessibility_id",
        "accessibility_name",
        $_POST["accessibility"],
    );

    // Update student information
    $query = "UPDATE STUDENTS SET
        students_name = :name,
        students_email = :email,
        students_phone_number = :phone,
        students_class_id = :class_id,
        students_country_id = :country_id,
        students_city_id = :city_id,
        students_school_id = :school_id,
        students_education_program_id = :program_id,
        students_status_id = :status_id,
        students_accessibility_id = :accessibility_id,
        students_last_updated = CAST(strftime('%s', 'now') AS INTEGER)
        WHERE students_id = :id";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing query", $db->lastErrorMsg());
    }

    $stmt->bindValue(":name", $_POST["student_name"], SQLITE3_TEXT);
    $stmt->bindValue(":email", $_POST["student_email"], SQLITE3_TEXT);
    $stmt->bindValue(":phone", $_POST["student_phone"], SQLITE3_TEXT);
    $stmt->bindValue(":class_id", $result_class_id, SQLITE3_INTEGER);
    $stmt->bindValue(":country_id", $result_country_id, SQLITE3_INTEGER);
    $stmt->bindValue(":city_id", $result_city_id, SQLITE3_INTEGER);
    $stmt->bindValue(":school_id", $result_school_id, SQLITE3_INTEGER);
    $stmt->bindValue(":program_id", $result_program_id, SQLITE3_INTEGER);
    $stmt->bindValue(":status_id", $result_status_id, SQLITE3_INTEGER);
    $stmt->bindValue(
        ":accessibility_id",
        $result_accessibility_id,
        SQLITE3_INTEGER,
    );
    $stmt->bindValue(":id", $_POST["student_id"], SQLITE3_INTEGER);

    $results = $stmt->execute();
    if (!$results) {
        errorMessages("Error executing query", $db->lastErrorMsg());
    }
    $_SESSION["success"] = "Student information updated successfully";
    header("Location: /NextStep/view.php?student_id=" . $_POST["student_id"]);
    $db->close();
    exit();
}

# Get data from the database
$row = full_students_database_query($db);

# Check to see there is a correct student id passed
if (!$row) {
    $_SESSION["error"] = "Student not found";
    header("Location: index.php");
    $db->close();
    exit();
}

$student_id = htmlspecialchars($row["students_id"]);
$student_name = htmlspecialchars($row["students_name"]);
$student_email = htmlspecialchars($row["students_email"]);
$student_phone_number = htmlspecialchars($row["students_phone_number"]);
$class_name = $row["class_name"];
$country_name = $row["country_name"];
$city_name = $row["city_name"];
$school_name = $row["school_name"];
$program_name = $row["program_name"];
$status_name = $row["status_name"];
$accessibility_name = $row["accessibility_name"];
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
<title>NextStep - Edit Student</title>
</head>
<body class="theme-<?= $color_theme ?>">
<?php include "../navbar.php"; ?>
<div class="page-box-wide">
<h2>Edit Student Information</h2>
<?php flashMessages(); ?>

<form method="POST" action="edit_student.php?student_id=<?= $student_id ?>">
<input type="hidden" name="student_id" value="<?= $student_id ?>" />

<label for="student_name">Name:</label>
<input type="text" id="student_name" name="student_name" value="<?= $student_name ?>" required/>

<label for="student_email">Email:</label>
<input type="email" id="student_email" name="student_email" value="<?= $student_email ?>" required/>

<label for="student_phone">Phone number:</label>
<input type="tel" id="student_phone" name="student_phone" value="<?= $student_phone_number ?>" required/>

<label for="class_name">Class name:</label>
<select id="class_name" name="class_name" required>
    <option value="">Select Class</option>
    <?php foreach ($class as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>" <?= $c === $class_name
    ? "selected"
    : "" ?>>
            <?= htmlspecialchars($c) ?>
        </option>
    <?php endforeach; ?>
</select>

<label for="country_name">Country:</label>
<select id="country_name" name="country_name" required>
    <option value="">Select Country</option>
    <?php foreach ($country as $cnt): ?>
        <option value="<?= htmlspecialchars($cnt) ?>" <?= $cnt === $country_name
    ? "selected"
    : "" ?>>
            <?= htmlspecialchars($cnt) ?>
        </option>
    <?php endforeach; ?>
</select>

<label for="city_name">City:</label>
<select id="city_name" name="city_name" required>
    <option value="">Select City</option>
    <?php foreach ($city as $cty): ?>
        <option value="<?= htmlspecialchars($cty) ?>" <?= $cty === $city_name
    ? "selected"
    : "" ?>>
            <?= htmlspecialchars($cty) ?>
        </option>
    <?php endforeach; ?>
</select>

<label for="school_name">School:</label>
<select id="school_name" name="school_name" required>
    <option value="">Select School</option>
    <?php foreach ($schools as $school): ?>
        <option value="<?= htmlspecialchars($school) ?>" <?= $school ===
$school_name
    ? "selected"
    : "" ?>>
            <?= htmlspecialchars($school) ?>
        </option>
    <?php endforeach; ?>
</select>

<label for="program_name">Program:</label>
<select id="program_name" name="program_name" required>
    <option value="">Select Program</option>
    <?php foreach ($education as $edu): ?>
        <option value="<?= htmlspecialchars($edu) ?>" <?= $edu === $program_name
    ? "selected"
    : "" ?>>
            <?= htmlspecialchars($edu) ?>
        </option>
    <?php endforeach; ?>
</select>

<label for="status">Status:</label>
<select id="status" name="status" required>
    <option value="">Select Status</option>
    <?php foreach ($status as $stat): ?>
        <option value="<?= htmlspecialchars($stat) ?>" <?= $stat ===
$status_name
    ? "selected"
    : "" ?>>
            <?= htmlspecialchars($stat) ?>
        </option>
    <?php endforeach; ?>
</select>

<label for="accessibility">Accessibility:</label>
<select id="accessibility" name="accessibility" required>
    <option value="">Select</option>
    <?php foreach ($accessibility as $acc): ?>
        <option value="<?= htmlspecialchars($acc) ?>" <?= $acc ===
$accessibility_name
    ? "selected"
    : "" ?>>
            <?= htmlspecialchars($acc) ?>
        </option>
    <?php endforeach; ?>
</select>


<div class="button-container">
    <input type="submit" class="simple-btn" name="submit" value="Save Changes">
    <a href="/NextStep/" class="simple-btn cancel-btn">Cancel</a>
</div>
</form>
</div>
<script src="../js/script.js"></script>
</body>
</html>
