<h2>General Settings</h2>
<?php flashMessages(); ?>

<form method="POST" action="handle_general.php">
    <h3 style="margin-top: 30px; margin-bottom: 15px;">Account Information</h3>

    <label for="account_name">Name:</label>
    <input type="text" id="account_name" name="account_name" value="<?= $teacher_name ?>" />

    <label for="account_email">Email:</label>
    <input type="email" id="account_email" name="account_email" value="<?= $teacher_email ?>" />

    <div class="button-container">
        <input type="submit" class="simple-btn" name="submit_general" value="Save Changes">
        <a href="index.php?tab=general" class="simple-btn cancel-btn">Cancel</a>
    </div>
</form>
