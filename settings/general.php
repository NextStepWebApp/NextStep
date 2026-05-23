<?php
require_once "../utils.php";
session_start();
loginSecurity();

$teacher_id = $_SESSION["teacher_id"];

$db = new SQLite3($db_file);

if (!isset($_POST["submit_general"])) {
    header("Location: index.php?tab=general");
    exit();
}

if (empty($_POST["account_name"]) || empty($_POST["account_email"])) {
    $_SESSION["error"] = "All fields are required";
    header("Location: index.php?tab=general");
    exit();
}

$teacher_name = trim($_POST["account_name"]);
$teacher_email = trim($_POST["account_email"]);

validate_teacher_name($db, $teacher_name, "settings");
validate_teacher_email($db, $teacher_email, "settings");

# Check if name or email exists
$stmt = $db->prepare("SELECT teacher_id FROM TEACHERS
                      WHERE (teacher_name = :name OR teacher_email = :email)
                      AND teacher_id != :id");

$stmt->bindValue(":name", $teacher_name);
$stmt->bindValue(":email", $teacher_email);
$stmt->bindValue(":id", $teacher_id);
$existing = $stmt->execute()->fetchArray();

if ($existing) {
    $_SESSION["error"] = "A teacher with this name or email already exists";
    header("Location: index.php?tab=general");
    exit();
}

# Update
$stmt = $db->prepare(
    "UPDATE TEACHERS SET teacher_name = :name, teacher_email = :email WHERE teacher_id = :id",
);
$stmt->bindValue(":name", $teacher_name);
$stmt->bindValue(":email", $teacher_email);
$stmt->bindValue(":id", $teacher_id);
$stmt->execute();

$_SESSION["success"] = "Settings updated successfully";

header("Location: index.php?tab=general");
exit();

<h2>General Settings</h2>
<?php flashMessages(); ?>

<form method="POST" action="general.php">
    <h3 class="extra-spacing">Account Information</h3>

    <label for="account_name">Name:</label>
    <input type="text" name="account_name" value="<?= $teacher_name ?>" />

    <label for="account_email">Email:</label>
    <input type="email" name="account_email" value="<?= $teacher_email ?>" />

    <div class="button-container">
        <input type="submit" class="simple-btn" name="submit_general" value="Save Changes">
        <a href="/NextStep/settings?tab=general" class="simple-btn cancel-btn">Cancel</a>
    </div>
</form>
