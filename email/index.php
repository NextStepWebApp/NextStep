<?php
session_start();
require_once "../utils.php";
require_once "../mailer.php";
setup_checker();
loginSecurity();
require_permission("email_compose");

if (empty($_SESSION["search"])) {
    header("Location: /NextStep/overview/");
    exit();
}

// Check internet connection before showing page
if (!is_connected()) {
    $_SESSION["error"] = "No internet connection. Please check your network and try again.";
    header("Location: /NextStep/overview/");
    exit();
}

try {
    $db = new SQLite3($db_file);
    $db->busyTimeout(10000);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}


// Check to see if teacher account is present and verified
$query = "SELECT verification_status FROM SMTP WHERE teacher_id = :id";
$stmt = $db->prepare($query);
if (!$stmt) {
    errorMessages("Error preparing insert query", $db->lastErrorMsg());
}
    
$teacher_id = $_SESSION["teacher_id"];
$stmt->bindValue(":id", $teacher_id, SQLITE3_INTEGER);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$result) {
    $_SESSION["error"] = "No email settings set up";
    $db->close();
    header("Location: /NextStep/overview/");
    exit();
}
if ($result["verification_status"] == EMAIL_UNVERIFIED) {
    $_SESSION["error"] = "Email settings not verified";
    $db->close();
    header("Location: /NextStep/settings/verification.php");
    exit();
}


// What came from the search and filter from utils
$search     = $_SESSION["search"];
$totalCount = query_students($db, $search, "count");
// Need to add the Yes filter, only those alumnus can get emails.
$search["accessibility_name"] = "Yes";

$totalCountYes = query_students($db, $search, "count");

if ($totalCountYes <= 0) {
    $_SESSION["error"] = "Cannot send emails with no alumnus selected";
    $db->close();
    header("Location: /NextStep/overview/");
    exit();
}

$alumnus_rows = [];

// The goal of this script is to put the information in the 
// email queue table
if (isset($_POST["submit_email"])) {
    set_time_limit(0);

    // Email data
    $subject = trim($_POST["subject"] ?? "");
    $body = trim($_POST["email_body"] ?? "");
    $type = EMAIL_NORMAL;
    $filter = implode(', ', array_filter($search));

    if (isset($_POST["include_info"]) && $_POST["include_info"] == "1") {
        $type = EMAIL_FULL;
    }

    if (empty($subject) || empty($body)) {
        $_SESSION["error"] = "Please fill in both the subject and the message body before sending";
        header("Location: /NextStep/email/");
        $db->close();
        exit();
    }

    // Insert email data in data table
    $query = "INSERT INTO EMAIL_DATA (
                data_email_type, 
                data_email_subject, 
                data_email_body,
                data_email_filter 
            ) VALUES (
                :type, 
                :subject, 
                :body,
                :filter
            );";
            
    $stmt = $db->prepare($query);
    if (!$stmt) {
        errorMessages("Error preparing insert query", $db->lastErrorMsg());
    }

    $stmt->bindValue(":type", $type, SQLITE3_INTEGER);
    $stmt->bindValue(":subject", $subject, SQLITE3_TEXT);
    $stmt->bindValue(":body", $body, SQLITE3_TEXT);
    $stmt->bindValue(":filter", $filter, SQLITE3_TEXT);

    $result = $stmt->execute();
    if (!$result) {
        errorMessages("Error executing insert query", $db->lastErrorMsg());
    }
    $data_id = $db->lastInsertRowID();
    
    $alumnus_rows = query_students($db, $search, "id");

    $queue_count = 0;
    foreach ($alumnus_rows as $alumni_row) {

        $student_id = $alumni_row["students_id"];
        $teacher_id = $_SESSION["teacher_id"];

        $query = "INSERT INTO EMAIL_QUEUE (
                    students_id, 
                    teacher_id, 
                    data_id, 
                    queue_attempts, 
                    queue_created_at
                ) VALUES (
                    :student, 
                    :teacher, 
                    :data,
                    :attempts,
                    :created
        );"; 

        $stmt = $db->prepare($query);
        if (!$stmt) {
            errorMessages("Error preparing insert query", $db->lastErrorMsg());
        }

        $stmt->bindValue(":student", $student_id, SQLITE3_INTEGER);
        $stmt->bindValue(":teacher", $teacher_id, SQLITE3_INTEGER);
        $stmt->bindValue(":data", $data_id, SQLITE3_INTEGER);
        $stmt->bindValue(":attempts", 0, SQLITE3_INTEGER);
        $stmt->bindValue(":created", date("Y-m-d H:i:s"), SQLITE3_TEXT);
         
        $result = $stmt->execute();
        if (!$result) {
            errorMessages("Error executing insert query", $db->lastErrorMsg());
        }

            $queue_count++;
    }

        $db->close();
        $_SESSION["success"] = "Added $queue_count email messages to the queue";
        unset($_SESSION["search"]);
        header("Location: /NextStep/overview/");
        exit();
    }


$school_name = $branding["school_name"];
$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);
$db->close();
?>

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="../images/logo.webp"/>
<link rel="stylesheet" href="../css/style_navbar.css"/>
<link rel="stylesheet" href="../css/style_page.css"/>

<!-- Quill snow theme -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />

<title>NextStep - Compose Email</title>
</head>

<body class="theme-<?= $color_theme ?>">
<?php include "../navbar.php"; ?>

<div class="page-box-wide">
    <?php flashMessages(); ?>
    <h2>Compose Email</h2>
    <?php if (!empty($search)): ?>
    <div class="active-filters">
        <span class="filters-label">Active filters:</span>
        <?php foreach ($search as $filter): ?>
            <span class="filter-tag"><?= htmlspecialchars($filter) ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="active-filters">
    <span class="filters-label">Your search found</span>
    <span class="filter-tag"><?= $totalCount ?></span>
    <span class="filters-label">
        <?= $totalCount === 1 ? 'alumni record' : 'alumni records' ?>.
    </span>

    <span class="filters-label">Sending to</span>
    <span class="filter-tag"><?= $totalCountYes ?></span>
    <span class="filters-label">
        <?= $totalCountYes === 1 ? 'alumnus' : 'alumni' ?>
        <?= $totalCountYes === 1 ? 'with' : 'with' ?>
        accessibility permission enabled.
    </span>
    </div>

    <hr class="compose-divider">

    <form method="POST" id="email-form">
        <div class="password-reset-container">
            <input type="checkbox" name="include_info" value="1" class="checkbox-input"/>
            <label class="checkbox-label">Include personal info block</label>
            <p class="help-text">Appends each alumnus their own data and an update link at the bottom of the email</p>
        </div> 

        <label class="compose-label" for="subject">Subject</label>
        <input type="text" name="subject" id="subject" placeholder="Enter email subject...">

        <!-- Quill -->
        <label class="compose-label" for="editor">Message</label>
        <div class="quill-wrapper">
            <div id="editor">
                <p><br /></p>
                <p><br /></p>
                <p><br /></p>
                <p><br /></p>
                <p><em>Sent via <a href="https://github.com/NextStepWebApp/NextStep/">NextStep</a> by <?= htmlspecialchars($school_name) ?></em></p>
            </div>
        </div>

        <input type="hidden" name="email_body">

        <div class="button-container">
        <button type="button" class="simple-btn" data-open-modal>
            Send to <?= $totalCountYes ?> <?= $totalCountYes === 1 ? 'recipient' : 'recipients' ?>
        </button>

        <dialog data-modal>
            <h2>Are you sure?</h2>
            <p>This will send the email to <?= $totalCountYes ?> <?= $totalCountYes === 1 ? 'recipient' : 'recipients' ?>.</p>
            <button type="submit" class="simple-btn" name="submit_email" form="email-form">Confirm Send</button>
            <button type="button" class="simple-btn" data-close-modal>Cancel</button>
        </dialog>

            <a href="/NextStep/overview/" class="simple-btn cancel-btn">Cancel</a>
        </div>

    </form>

</div>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="../js/script.js"></script>
<script>
    const quill = new Quill('#editor', {
        theme: 'snow'
    });

    quill.on('text-change', () => {
        document.querySelector('input[name="email_body"]').value = quill.getSemanticHTML();
    });
</script>
</body>
</html>