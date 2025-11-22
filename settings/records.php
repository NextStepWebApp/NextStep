<?php
$items_per_page = 5;

// Handle form submissions FIRST (before any output)
if (isset($_POST["config_action"]) && isset($_POST["config_section"])) {
    $action = $_POST["config_action"];
    $section = $_POST["config_section"];
    
    // Reload config fresh from file to get latest state
    $config = json_decode(file_get_contents($config_path), true);
    
    // Validate section exists in config
    if (!isset($config[$section])) {
        $_SESSION['error'] = "Invalid section";
        header("Location: index.php?tab=records");
        exit();
    }
    
    if ($action === "add") {
        if (empty($_POST["config_value"])) {
            $_SESSION['error'] = "Value cannot be empty";
        } else {
            // Split by comma and process each value
            $values = array_map('trim', explode(',', $_POST["config_value"]));
            $values = array_filter($values, fn($v) => $v !== ''); // Remove empty
            
            $added = 0;
            $skipped = 0;
            $errors = [];
            
            foreach ($values as $value) {
                if (strlen($value) > 255) {
                    $errors[] = "'$value' is too long";
                } elseif (in_array($value, $config[$section])) {
                    $skipped++;
                } else {
                    $config[$section][] = $value;
                    $added++;
                }
            }
            
            if ($added > 0) {
                if (file_put_contents($config_path, json_encode($config, JSON_PRETTY_PRINT)) === false) {
                    $_SESSION['error'] = "Failed to save configuration";
                } else {
                    $msg = "$added item(s) added";
                    if ($skipped > 0) $msg .= ", $skipped already existed";
                    if (!empty($errors)) $msg .= ", " . count($errors) . " had errors";
                    $_SESSION['success'] = $msg;
                }
            } else {
                if ($skipped > 0) {
                    $_SESSION['error'] = "All items already exist";
                } elseif (!empty($errors)) {
                    $_SESSION['error'] = implode(", ", $errors);
                }
            }
        }
        header("Location: index.php?tab=records");
        exit();
        
    } elseif ($action === "remove") {
        if (!isset($_POST["config_index"])) {
            $_SESSION['error'] = "Invalid request";
        } else {
            $index = (int)$_POST["config_index"];
            
            if ($index < 0 || $index >= count($config[$section])) {
                $_SESSION['error'] = "Invalid option index";
            } else {
                array_splice($config[$section], $index, 1);
                if (file_put_contents($config_path, json_encode($config, JSON_PRETTY_PRINT)) === false) {
                    $_SESSION['error'] = "Failed to save configuration";
                } else {
                    $_SESSION['success'] = "Option removed successfully";
                }
            }
        }
        header("Location: index.php?tab=records");
        exit();
    }
}

// RELOAD config fresh before displaying (this is the key fix!)
$config = json_decode(file_get_contents($config_path), true);
?>
<h2>Manage options for student records</h2>
<?php flashMessages(); ?>
<style>
.config-table-wrapper {
    margin: 30px 0;
}
.config-table-wrapper table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
.config-table-wrapper th {
    background: #f9fafb;
    color: #0b1d59;
    padding: 12px 24px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    border-bottom: 2px solid #e5e7eb;
}
.config-table-wrapper td {
    padding: 12px 24px;
    border-bottom: 1px solid #e5e7eb;
    color: #0b1d59;
    font-size: 14px;
}
.config-table-wrapper tr:last-child td {
    border-bottom: none;
}
.config-table-wrapper tr:hover td {
    background-color: rgba(11, 29, 89, 0.05);
}
.show-more-btn {
    width: 100%;
    padding: 10px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    color: #0b1d59;
    font-weight: 600;
    cursor: pointer;
    margin-top: 10px;
    transition: all 0.2s ease;
}
.show-more-btn:hover {
    background: #f3f4f6;
}
.hidden-rows {
    display: none;
}
</style>
<?php foreach ($config as $section => $section_data): 
    $label = ucwords(str_replace('_', ' ', $section));
    $has_more = count($section_data) > $items_per_page;
    $extra_count = count($section_data) - $items_per_page;
?>
<div class="config-table-wrapper" id="section-<?= htmlspecialchars($section) ?>">
    <h3><?= htmlspecialchars($label) ?></h3>
    <table>
        <tbody>
        <?php if (empty($section_data)): ?>
            <tr><td colspan="2" style="text-align:center;color:#9ca3af;">No items</td></tr>
        <?php else: ?>
            <?php foreach ($section_data as $index => $value): ?>
                <tr class="<?= ($index >= $items_per_page) ? "hidden-rows hidden-rows-$section" : '' ?>">
                    <td><?= htmlspecialchars($value) ?></td>
                    <td style="text-align:right;">
                        <form method="POST" action="index.php?tab=records">
                            <input type="hidden" name="config_action" value="remove">
                            <input type="hidden" name="config_section" value="<?= htmlspecialchars($section) ?>">
                            <input type="hidden" name="config_index" value="<?= $index ?>">
                            <button type="submit" class="simple-btn" style="background:#fee2e2;color:#991b1b;">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($has_more): ?>
        <button class="show-more-btn" data-count="<?= $extra_count ?>" onclick="toggleRows('<?= htmlspecialchars($section) ?>', this)">
            Show More (<?= $extra_count ?> more)
        </button>
    <?php endif; ?>
    
    <form method="POST" action="index.php?tab=records" style="margin-top:15px;display:flex;gap:10px;">
        <input type="hidden" name="config_action" value="add">
        <input type="hidden" name="config_section" value="<?= htmlspecialchars($section) ?>">
        <input type="text" name="config_value" placeholder="Add <?= strtolower(htmlspecialchars($label)) ?> (comma-separated for multiple)..." required>
        <button type="submit" class="simple-btn" style="background:#0b1d59;color:white;">Add</button>
    </form>
</div>
<?php endforeach; ?>
<script>
function toggleRows(section, btn) {
    const rows = document.querySelectorAll('.hidden-rows-' + section);
    if (rows.length === 0) return;
    
    const isCurrentlyHidden = rows[0].classList.contains('hidden-rows');
    const count = btn.getAttribute('data-count');
    
    rows.forEach(row => {
        if (isCurrentlyHidden) {
            row.classList.remove('hidden-rows');
        } else {
            row.classList.add('hidden-rows');
        }
    });
    
    btn.textContent = isCurrentlyHidden ? 'Show Less' : `Show More (${count} more)`;
}

// Restore scroll position after form submit
const savedScroll = sessionStorage.getItem('dataTabScroll');
if (savedScroll) {
    window.scrollTo(0, parseInt(savedScroll));
    sessionStorage.removeItem('dataTabScroll');
}

// Save scroll position before form submit
document.querySelectorAll('.config-table-wrapper form').forEach(form => {
    form.addEventListener('submit', () => {
        sessionStorage.setItem('dataTabScroll', window.scrollY.toString());
    });
});
</script>
