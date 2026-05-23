<?php
require_once "../utils.php";
loginSecurity();
$teacher_id = $_SESSION["teacher_id"];
$db = new SQLite3($db_file);

if (isset($_POST["submit_general"])) {
    if (empty($_POST["account_name"]) || empty($_POST["account_email"])) {
        $_SESSION["error"] = "All fields are required";
        header("Location: /NextStep/settings/?tab=general");
        $db->close();
        exit();
    }

    $teacher_name = trim($_POST["account_name"]);
    $teacher_email = trim($_POST["account_email"]);
    validate_teacher_name($db, $teacher_name, "settings");
    validate_teacher_email($db, $teacher_email, "settings");

    $stmt = $db->prepare("SELECT teacher_id FROM TEACHERS
                          WHERE (teacher_name = :name OR teacher_email = :email)
                          AND teacher_id != :id");
    $stmt->bindValue(":name", $teacher_name, SQLITE3_TEXT);
    $stmt->bindValue(":email", $teacher_email, SQLITE3_TEXT);
    $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
    $existing = $stmt->execute()->fetchArray();

    if ($existing) {
        $_SESSION["error"] = "A teacher with this name or email already exists";
        header("Location: /NextStep/settings/?tab=general");
        $db->close();
        exit();
    }

    $stmt = $db->prepare(
        "UPDATE TEACHERS SET teacher_name = :name, teacher_email = :email WHERE teacher_id = :id"
    );
    $stmt->bindValue(":name", $teacher_name, SQLITE3_TEXT);
    $stmt->bindValue(":email", $teacher_email, SQLITE3_TEXT);
    $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
    $stmt->execute();

    $_SESSION["success"] = "Settings updated successfully";
    header("Location: /NextStep/settings/?tab=general");
    $db->close();
    exit();
}

# Fetch current values for the form
$stmt = $db->prepare("SELECT teacher_name, teacher_email FROM TEACHERS WHERE teacher_id = :id");
$stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
$row = $stmt->execute()->fetchArray();
$teacher_name = $row["teacher_name"] ?? "";
$teacher_email = $row["teacher_email"] ?? "";

$db->close();
?>

<h2>General Settings</h2>
<?php flashMessages(); ?>
<form method="POST" action="/NextStep/settings/?tab=general">
    <h3 class="extra-spacing">Account Information</h3>
    <label for="account_name">Name:</label>
    <input type="text" id="account_name" name="account_name" value="<?= htmlspecialchars($teacher_name) ?>" />
    <label for="account_email">Email:</label>
    <input type="email" id="account_email" name="account_email" value="<?= htmlspecialchars($teacher_email) ?>" />
    <div class="button-container">
        <input type="submit" class="simple-btn" name="submit_general" value="Save Changes">
        <a href="/NextStep/settings?tab=general" class="simple-btn cancel-btn">Cancel</a>
    </div>
</form>
