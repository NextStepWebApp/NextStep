<?php
session_start();
require_once "utils.php";
setup_checker();
if (!isset($_SESSION["teacher_username"])) {
    header("Location: login.php");
    exit();
}

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    errorMessages("Database connection failed", $e->getMessage());
}
$color_theme = color_theme_helper($db, $color_theme_system["theme_color"]);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="images/logo.webp"/>
<link rel="stylesheet" href="css/style_navbar.css"/>
<link rel="stylesheet" href="css/style_page.css"/>
<title>NextStep - Home</title>
<style>
    .home-wrapper {
        max-width: 1100px;
        width: 90%;
        margin: 120px auto 50px;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Greeting */
    .home-greeting {
        color: var(--color-white);
        font-size: 22px;
        font-weight: 700;
        opacity: 0.95;
        padding: 0 4px;
    }
    .home-greeting span {
        opacity: 0.7;
        font-weight: 400;
        font-size: 15px;
        display: block;
        margin-top: 4px;
    }

    /* Stats row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    .stat-card {
        background: var(--color-white);
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 8px 24px var(--shadow-xl);
        border: 1px solid var(--overlay-white);
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .stat-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--color-text-muted);
    }
    .stat-value {
        font-size: 32px;
        font-weight: 800;
        color: var(--color-primary);
        line-height: 1;
    }
    .stat-delta {
        font-size: 12px;
        color: var(--color-text-muted);
        margin-top: 2px;
    }
    .stat-delta.up { color: #059669; }
    .stat-delta.down { color: #dc2626; }

    /* Two column layout */
    .home-two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    /* Shared card style */
    .home-card {
        background: var(--color-white);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 8px 24px var(--shadow-xl);
        border: 1px solid var(--overlay-white);
    }
    .home-card-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--color-text-muted);
        margin-bottom: 18px;
    }

    /* Activity feed */
    .activity-list {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--color-border-gray);
    }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        margin-top: 4px;
        flex-shrink: 0;
        background: var(--color-primary);
        opacity: 0.5;
    }
    .activity-dot.new { opacity: 1; background: var(--color-accent); }
    .activity-dot.updated { opacity: 1; background: #059669; }
    .activity-dot.flagged { opacity: 1; background: #f59e0b; }
    .activity-text {
        font-size: 13px;
        color: var(--color-primary);
        line-height: 1.45;
        flex: 1;
    }
    .activity-text strong { font-weight: 700; }
    .activity-time {
        font-size: 11px;
        color: var(--color-text-lighter);
        white-space: nowrap;
        margin-top: 2px;
    }

    /* Quick actions */
    .qa-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .qa-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        background: var(--color-bg-light);
        border: 1px solid var(--color-border-light);
        border-radius: 12px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
        color: var(--color-primary);
    }
    .qa-btn:hover {
        background: var(--color-bg-gray);
        border-color: var(--color-border-medium);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px var(--shadow-md);
    }
    .qa-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
        background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light));
        color: white;
    }
    .qa-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--color-primary);
        line-height: 1.2;
    }
    .qa-desc {
        font-size: 11px;
        color: var(--color-text-muted);
        margin-top: 2px;
    }

    /* Responsive */
    @media (max-width: 1000px) {
        .home-wrapper { margin-top: 150px; }
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .home-two-col { grid-template-columns: 1fr; }
        .home-wrapper { margin-top: 200px; }
    }
    @media (max-width: 480px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .qa-grid { grid-template-columns: 1fr; }
    }
</style>
</head>
<body class="theme-<?= $color_theme ?>">
<?php include "navbar.php"; ?>
<?php flashMessages(); ?>

<div class="home-wrapper">

    <div class="home-greeting">
        Welcome back<?php if (!empty($_SESSION["teacher_username"])) {
            echo ", " . htmlspecialchars($_SESSION["teacher_username"]);
        } ?> 👋
        <span>Here's what's happening with your alumni today.</span>
    </div>

    <!-- Stats row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Alumni</div>
            <div class="stat-value">
                <?php
                $total = $db->querySingle("SELECT COUNT(*) FROM students");
                echo $total !== false ? (int) $total : 0;
                ?>
            </div>
            <div class="stat-delta">all records</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Added this month</div>
            <div class="stat-value">
                <?php
                $month = $db->querySingle(
                    "SELECT COUNT(*) FROM students WHERE strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')",
                );
                echo $month !== false ? (int) $month : 0;
                ?>
            </div>
            <div class="stat-delta up">↑ this month</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Teachers</div>
            <div class="stat-value">
                <?php
                $teachers = $db->querySingle("SELECT COUNT(*) FROM teachers");
                echo $teachers !== false ? (int) $teachers : 0;
                ?>
            </div>
            <div class="stat-delta">active accounts</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Uncontacted</div>
            <div class="stat-value">
                 /* Adjust the WHERE clause to match your actual status column/value */<?php
                 $uncontacted = $db->querySingle(
                     "SELECT COUNT(*) FROM students WHERE status IS NULL OR status = '' OR status = 'uncontacted'",
                 );
                 echo $uncontacted !== false ? (int) $uncontacted : 0;
                 ?>
            </div>
            <div class="stat-delta down">need follow-up</div>
        </div>
    </div>

    <!-- Two column: activity + quick actions -->
    <div class="home-two-col">

        <!-- Recent activity -->
        <div class="home-card">
            <div class="home-card-title">Recent activity</div>
            <ul class="activity-list">
                 /* Pull the 6 most recently modified alumni records as a proxy for activity.
 Adjust table/column names to match your schema. */<?php
 $recent = $db->query(
     "SELECT name, status, updated_at FROM students ORDER BY updated_at DESC LIMIT 6",
 );
 $has_rows = false;
 if ($recent) {
     while ($row = $recent->fetchArray(SQLITE3_ASSOC)) {
         $has_rows = true;
         $name = htmlspecialchars($row["name"] ?? "Unknown");
         $status = htmlspecialchars($row["status"] ?? "");
         $time = !empty($row["updated_at"])
             ? htmlspecialchars($row["updated_at"])
             : "recently";
         $dot_class = "";
         if ($status === "uncontacted" || $status === "") {
             $dot_class = "";
         } elseif (
             str_contains(strtolower($status), "employ") ||
             str_contains(strtolower($status), "enroll")
         ) {
             $dot_class = "updated";
         } else {
             $dot_class = "new";
         }
         echo "
                        <li class='activity-item'>
                            <span class='activity-dot {$dot_class}'></span>
                            <div style='flex:1'>
                                <div class='activity-text'><strong>{$name}</strong> — {$status}</div>
                                <div class='activity-time'>{$time}</div>
                            </div>
                        </li>";
     }
 }
 if (!$has_rows) {
     echo "<li class='activity-item'><div class='activity-text' style='color:var(--color-text-lighter)'>No recent activity yet.</div></li>";
 }
 ?>
            </ul>
        </div>

        <!-- Quick actions -->
        <div class="home-card">
            <div class="home-card-title">Quick actions</div>
            <div class="qa-grid">
                <a href="create_student.php" class="qa-btn">
                    <div class="qa-icon">+</div>
                    <div>
                        <div class="qa-label">Add alumni</div>
                        <div class="qa-desc">Create a new record</div>
                    </div>
                </a>
                <a href="index.php" class="qa-btn">
                    <div class="qa-icon">✉</div>
                    <div>
                        <div class="qa-label">Compose email</div>
                        <div class="qa-desc">Go to Overview → select</div>
                    </div>
                </a>
                <a href="students.php" class="qa-btn">
                    <div class="qa-icon">⊞</div>
                    <div>
                        <div class="qa-label">View alumni</div>
                        <div class="qa-desc">Browse all records</div>
                    </div>
                </a>
                <a href="teachers.php" class="qa-btn">
                    <div class="qa-icon">👤</div>
                    <div>
                        <div class="qa-label">Teachers</div>
                        <div class="qa-desc">Manage accounts</div>
                    </div>
                </a>
            </div>
        </div>

    </div>
</div>

<script src="js/script.js"></script>
</body>
</html>
