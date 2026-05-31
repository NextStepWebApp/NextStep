<?php
session_start();
require_once "../utils.php";
require_once "../email_utils.php";
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
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}

// What came from the search and filter from utils
$search     = $_SESSION["search"];
$totalCount = query_students($db, $search, "count");

// Need to add the Yes filter, only those alumnus can get emails.
$search["accessibility_name"] = "Yes";

$totalCountYes = query_students($db, $search, "count");

if ($totalCountYes <= 0) {
    $_SESSION["error"] = "Cannot send emails with no alumnus selected";
    header("Location: /NextStep/overview/");
    exit();
}

$alumnus_rows = [];

// The goal of this script is to put the information in the 
// email queue table
if (isset($_POST["submit_email"])) {
    set_time_limit(0);

    $subject      = trim($_POST["subject"] ?? "");
    $body         = trim($_POST["email_body"] ?? "");
    
    $update_email = false;
    if (isset($_POST["include_info"]) && $_POST["include_info"] == "1") {
        $update_email = true;
    }

    $alumnus_rows = query_students($db, $search, "id");

    if (empty($subject) || empty($body)) {
        $_SESSION["error"] = "Please fill in both the subject and the message body before sending";
        header("Location: /NextStep/email/");
        exit();
    } else {
        $queue_count = 0;

        foreach ($alumnus_rows as $alumni_row) {
            $query = ""; 
          

            // your PHPMailer send here
            $queue_count++;
        }

        header("Location: /NextStep/overview/");
        exit();
    }
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

    <div class="password-reset-container">
        <input type="checkbox" name="include_info" value="1" class="checkbox-input"/>
        <label class="checkbox-label">Include personal info block</label>
        <p class="help-text">Appends each alumnus their own data and an update link at the bottom of the email</p>
    </div>

    <hr class="compose-divider">

    <form method="POST" id="email-form">

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
                <p><em>Sent via <a href="https://github.com/NextStepWebApp/NextStep/">NextStep</a> Alumni Tracking by <?= htmlspecialchars($school_name) ?></em></p>
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