<?php

require_permission("system_records");

$items_per_page = 5;

# Handle form submissions
if (isset($_POST["config_action"]) && isset($_POST["config_section"])) {
    $action = $_POST["config_action"];
    $section = $_POST["config_section"];

    # Reload the config json to get it fresh
    $config = json_decode(file_get_contents($config_path), true);

    # Validate section exists in config
    if (!isset($config[$section])) {
        $_SESSION['error'] = "Invalid section";
        header("Location: /NextStep/settings/?tab=records");
        exit();
    }

    if ($action === "add") {
        if (empty($_POST["config_value"])) {
            $_SESSION['error'] = "Value cannot be empty";
        } else {
            # Split by comma and process each value
            $values = array_map('trim', explode(',', $_POST["config_value"]));
            $values = array_filter($values, fn($v) => $v !== ''); # Remove empty

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
        header("Location: /NextStep/settings/?tab=records");
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
        header("Location: /NextStep/settings?tab=records");
        exit();
    }
}

# RELOAD config fresh before displaying
$config = json_decode(file_get_contents($config_path), true);
?>
    <h2>Manage options for student records</h2>
    <?php flashMessages(); ?>

    <?php foreach ($config as $section => $section_data):
        $label = ucwords($section);
        $has_more = count($section_data) > $items_per_page;
        $extra_count = count($section_data) - $items_per_page;
    ?>
    <div class="config-table-wrapper">
        <h3 class="extra-spacing"><?= htmlspecialchars($label) ?></h3>

        <?php if (empty($section_data)): ?>
            <p class="no-items">No items</p>
        <?php else: ?>
            <table>
                <tbody>
                    <?php foreach ($section_data as $index => $value): ?>
                        <tr class="<?= ($index >= $items_per_page) ? "hidden-rows hidden-rows-$section" : '' ?>">
                            <td><?= htmlspecialchars($value) ?></td>
                            <td>
                                <form method="POST" action="/NextStep/settings/?tab=records">
                                    <input type="hidden" name="config_action" value="remove">
                                    <input type="hidden" name="config_section" value="<?= htmlspecialchars($section) ?>">
                                    <input type="hidden" name="config_index" value="<?= $index ?>">
                                    <button type="submit" class="simple-btn">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($has_more): ?>
                <button class="show-more-btn" data-count="<?= $extra_count ?>" onclick="toggleRows('<?= htmlspecialchars($section) ?>', this)">
                    Show More (<?= $extra_count ?> more)
                </button>
            <?php endif; ?>
        <?php endif; ?>

        <button class="simple-btn big-plus" data-open-modal>+</button>
        <dialog data-modal class="wide-textbox">
            <h2>Add <?= htmlspecialchars($label) ?></h2>
            <form method="POST" action="/NextStep/settings/?tab=records">
                <input type="hidden" name="config_action" value="add">
                <input type="hidden" name="config_section" value="<?= htmlspecialchars($section) ?>">
                <textarea name="config_value" placeholder="Add <?= strtolower(htmlspecialchars($label)) ?> (comma-separated for multiple)..." required></textarea>
                <div>
                    <button type="submit" class="simple-btn">Add</button>
                    <button type="button" class="simple-btn" data-close-modal>Cancel</button>
                </div>
            </form>
        </dialog>
    </div>
    <?php endforeach; ?>

<script>
// Restore scroll position after form submit
const savedScroll = sessionStorage.getItem('dataTabScroll');
if (savedScroll) {
    window.scrollTo(0, parseInt(savedScroll));
    sessionStorage.removeItem('dataTabScroll');
}

// Save scroll position before form submit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', () => {
        sessionStorage.setItem('dataTabScroll', window.scrollY.toString());
    });
});
</script>
