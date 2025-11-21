<h2>Data Configuration</h2>
<p>Manage dropdown options for student records.</p>

<?php
$config_path = "../config/config.json";
$config_data = json_decode(file_get_contents($config_path), true);

$config_sections = [
    'accessibility' => 'Accessibility Options',
    'city' => 'Cities',
    'class' => 'Classes',
    'country' => 'Countries',
    'education' => 'Education Programs',
    'school' => 'Schools',
    'status' => 'Status Options'
];

$items_per_page = 10;
?>

<style>
/* your big CSS block unchanged */
</style>

<?php foreach ($config_sections as $key => $label): ?>
    <div class="config-table-wrapper">
        <h3><?= htmlspecialchars($label) ?></h3>

        <table>
            <tbody>
            <?php if (empty($config_data[$key])): ?>
                <tr><td colspan="2" style="text-align:center;color:#9ca3af;">No items yet</td></tr>
            <?php else: ?>
                <?php foreach ($config_data[$key] as $index => $value): ?>
                    <tr class="<?= $index >= $items_per_page ? 'hidden-rows hidden-rows-'.$key : '' ?>">
                        <td><?= htmlspecialchars($value) ?></td>
                        <td>
                            <form method="POST" action="index.php?tab=data">
                                <input type="hidden" name="config_action" value="remove">
                                <input type="hidden" name="config_section" value="<?= $key ?>">
                                <input type="hidden" name="config_index" value="<?= $index ?>">
                                <button type="submit" class="simple-btn" style="background:#fee2e2;color:#991b1b;">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <?php if (!empty($config_data[$key]) && count($config_data[$key]) > $items_per_page): ?>
            <button class="show-more-btn" onclick="toggleRows('<?= $key ?>', this)">
                Show More (<?= count($config_data[$key]) - $items_per_page ?> more)
            </button>
        <?php endif; ?>

        <form method="POST" action="index.php?tab=data" style="margin-top:15px;display:flex;gap:10px;">
            <input type="hidden" name="config_action" value="add">
            <input type="hidden" name="config_section" value="<?= $key ?>">
            <input type="text" name="config_value" placeholder="Add new <?= strtolower($label) ?> option..." required>
            <button type="submit" class="simple-btn" style="background:#0b1d59;color:white;">Add</button>
        </form>

        <?php if ($key === 'class' || $key === 'accessibility'): ?>
            <p style="margin-top:10px;background:#fef3c7;padding:10px;border:1px solid #fbbf24;border-radius:6px;">
                ⚠️ Removing items here will NOT delete existing student records.
            </p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<script>
/* toggleRows function unchanged */
</script>
