<?php
require_once "../utils.php";
loginSecurity();
$teacher_id = $_SESSION["teacher_id"];

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

if (isset($_POST["submit_general"])) {
    if (empty($_POST["account_name"])) {
        $_SESSION["error"] = "Account name is required";
        header("Location: /NextStep/settings/?tab=general");
        $db->close();
        exit();
    }

    $teacher_name = trim($_POST["account_name"]);
    validate_teacher_name($db, $teacher_name, "settings");


    $stmt = $db->prepare(
        "UPDATE TEACHERS SET teacher_name = :name  WHERE teacher_id = :id"
    );
    $stmt->bindValue(":name", $teacher_name, SQLITE3_TEXT);
    $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
    $stmt->execute();

    $_SESSION["success"] = "Settings updated successfully";
    header("Location: /NextStep/settings/?tab=general");
    $db->close();
    exit();
}

# Fetch current values for the form
$stmt = $db->prepare("SELECT teacher_name FROM TEACHERS WHERE teacher_id = :id");
$stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
$row = $stmt->execute()->fetchArray();
$teacher_name = $row["teacher_name"] ?? "";

$db->close();
?>

<?php flashMessages(); ?>
<h2>General Settings</h2>
<form method="POST" action="/NextStep/settings/?tab=general">

    <!-- ACCOUNT INFORMATION -->
    <h3 class="extra-spacing">Account Information</h3>
    <label for="account_name">Name:</label>
    <input type="text" id="account_name" name="account_name" value="<?= htmlspecialchars($teacher_name) ?>" />

    <div class="button-container">
        <input type="submit" class="simple-btn" name="submit_general" value="Save Changes">
        <a href="/NextStep/settings?tab=general" class="simple-btn cancel-btn">Cancel</a>
    </div>

</form>