<?php
require_once "../utils.php";
loginSecurity();
$teacher_id = $_SESSION["teacher_id"];

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
    exit();
}

if (isset($_POST["submit_smtp"])) {
    if (empty($_POST["smtp_email"]) || empty($_POST["smtp_host"]) || 
        empty($_POST["smtp_port"]) || empty($_POST["smtp_password"])) {
        $_SESSION["error"] = "All fields are required. Please fill in all settings.";
        $db->close();
        header("Location: /NextStep/settings/?tab=email");
        exit();
    }

    $smtp_email = trim($_POST["smtp_email"]);
    $smtp_host = trim($_POST["smtp_host"]);
    $smtp_port = intval($_POST["smtp_port"]);
    $smtp_password = $_POST["smtp_password"];

    validate_teacher_email($db, $smtp_email, "settings");

  // Check if SMTP settings for this teacher already exist
    $stmt = $db->prepare("SELECT teacher_id FROM SMTP WHERE teacher_id = :id");
    $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
    $existing = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if ($existing) {
        $query = "UPDATE SMTP 
            SET smtp_email = :email, smtp_host = :host, smtp_port = :port, smtp_password = :password 
            WHERE teacher_id = :id";
        $stmt = $db->prepare($query);
    } else {
        $query = "INSERT INTO SMTP (teacher_id, smtp_email, smtp_host, smtp_port, smtp_password) 
                  VALUES (:id, :email, :host, :port, :password)";
        $stmt = $db->prepare($query);
    }

    $stmt->bindValue(":password", $smtp_password, SQLITE3_TEXT);
    $stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
    $stmt->bindValue(":email", $smtp_email, SQLITE3_TEXT);
    $stmt->bindValue(":host", $smtp_host, SQLITE3_TEXT);
    $stmt->bindValue(":port", $smtp_port, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        $_SESSION["success"] = "SMTP settings saved successfully!";
    } else {
        $_SESSION["error"] = "Failed to save SMTP settings.";
    }
    $db->close(); 
    header("Location: /NextStep/settings/?tab=email");
    exit();
}


$stmt = $db->prepare("SELECT smtp_email, smtp_host, smtp_port, smtp_password FROM SMTP WHERE teacher_id = :id");
$stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
$settings_exist = false;

if ($result) {
    $settings_exist = true;
    $smtp_email = $result["smtp_email"];
    $smtp_host  = $result["smtp_host"];
    $smtp_port  = $result["smtp_port"];
    $smtp_password = $result["smtp_password"];
} else {
    $smtp_email = "";
    $smtp_host  = "";
    $smtp_port  = ""; 
    $smtp_password = "";
}

$db->close();
?>

<?php flashMessages(); ?>
<h2>Email Settings</h2>
<form method="POST" action="/NextStep/settings/?tab=email">

    <!-- EMAIL SETTINGS -->
    <h3 class="extra-spacing">Email Server Settings (SMTP)</h3>
   
    <?php if (!$settings_exist): ?>
        <a href="https://www.youtube.com/@MelchizedekShah" target="_blank" class="simple-btn">Watch Setup Tutorial</a>
        <br />
        <br />
    <?php endif; ?>

    <label for="smtp_email">SMTP Email / Username:</label>
    <input type="text" id="smtp_email" name="smtp_email" value="<?= htmlspecialchars($smtp_email) ?>" placeholder="your-email@example.com" />

    <label for="smtp_host">SMTP Host:</label>
    <input type="text" id="smtp_host" name="smtp_host" value="<?= htmlspecialchars($smtp_host) ?>" placeholder="smtp.example.com" />

    <label for="smtp_port">SMTP Port:</label>
    <input type="number" id="smtp_port" name="smtp_port" value="<?= htmlspecialchars($smtp_port) ?>" placeholder="465" />

    <label for="smtp_password">App Password:</label>


    <div class="input-group">
    <input
        type="password"
        name="smtp_password"
        placeholder="•••• •••• •••• ••••"
        id="smtp_password"
        value="<?= htmlspecialchars($smtp_password) ?>"
        class="password-input"
    />
    <span class="toggle-password" 
          id="smtp_toggle_btn"
          onclick="togglePassword('smtp_password', 'smtp_toggle_btn')">
        Show
    </span>
    </div>


    <div class="button-container">
        <input type="submit" class="simple-btn" name="submit_smtp" value="Save Changes">
        <a href="/NextStep/settings?tab=general" class="simple-btn cancel-btn">Cancel</a>
    </div>

  </form>