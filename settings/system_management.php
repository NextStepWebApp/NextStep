<?php

if (isset($_POST["nextstep-reset"])) {
    if (isset($_POST["password"])) {
        $password = trim($_POST["password"] ?? "");

        if ($password === "") {
            $_SESSION["error"] = "Password is required";
            header("Location: /NextStep/settings/?tab=system_management");
            exit();
        }

        # Connect to the database to check if the password is correct
        try {
            $db = new SQLite3($db_file);
        } catch (Exception $e) {
            errorMessages("Database connection failed", $e->getMessage());
        }

        # Get all the info you need from the user
        $query = "SELECT teacher_password, teacher_role_id
            FROM TEACHERS
            WHERE teacher_id = :teacher_id";

        $stmt = $db->prepare($query);
        $stmt->bindValue(
            ":teacher_id",
            $_SESSION["teacher_id"],
            SQLITE3_INTEGER,
        );
        $result = $stmt->execute();
        $row = $result->fetchArray();

        # Get the role id for admin (only admins can reset)
        $role_id = get_foreign_key_roles($db, "ADMIN");

        # Check if user exists
        if (!$row) {
            $_SESSION["error"] = "User not found";
            $db->close();
            header("Location: /NextStep/settings/?tab=system_management");
            exit();
        }

        if (
            password_verify($password, $row["teacher_password"]) &&
            (int) $row["teacher_role_id"] === (int) $role_id # Only admins
        ) {
            # Reset part
            $db->close();

            # Filepath to the setup.json
            $setup_config_path = $nextstep_config["setup_config_path"];
            if (!file_exists($setup_config_path)) {
                die("setup.json not found at $setup_config_path\n");
            }
            $setup_config = json_decode(
                file_get_contents($setup_config_path),
                true,
            );

            # Change the setup_value to 0 to trigger setup
            $setup_config["setup_value"] = 0;
            # Save the updated JSON back to the file
            if (
                file_put_contents(
                    $setup_config_path,
                    json_encode($setup_config, JSON_PRETTY_PRINT),
                ) === false
            ) {
                error_log("Failed to update setup.json\n");
                die("setup.json could not be updated $setup_config_path\n");
            }

            header("Location: /NextStep/");
            exit();
        } else {
            # Wrong password or no permission
            $_SESSION["error"] = "Invalid password";
            $db->close();
            header("Location: /NextStep/settings/?tab=system_management");
            exit();
        }
    }
} ?>
<?php flashMessages(); ?>
<h2>System Management</h2>
<form method="POST" action="/NextStep/settings/?tab=system_management">
    <h3 class="extra-spacing">DATABASE RESET</h3>
    <label for="password">Enter Password to Confirm Reset:</label>
    <input type="password" id="password" name="password" placeholder="Enter your password" required>
    <div class="button-container">
        <input type="submit" class="simple-btn" name="nextstep-reset" value="RESET NEXTSTEP">
    </div>
</form>
