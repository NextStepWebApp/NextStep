<?php

# function that generates a password with alternating characters and numbers
function genPassword(int $length)
{
    $password = "";
    $letters = range("a", "z");
    for ($i = 0; $i < $length; $i++) {
        if ($i % 2 == 0) {
            $password .= rand(0, 9);
        } else {
            $index = rand(0, count($letters) - 1);
            $password .= $letters[$index];
        }
    }
    return $password;
}

# funtion to save files with information
function savefile(string $location, string $name, string $text)
{
    $filename = "{$location}/{$name}";

    if (!is_dir($location)) {
        mkdir($location, 0777, true);
    }

    if (file_put_contents($filename, $text) !== false) {
        echo " - File '$filename' created successfully!\n";
    } else {
        echo " - Error: Could not create file.\n";
    }
}

function loginSecurity()
{
    if (!isset($_SESSION["teacher_username"])) {
        $_SESSION["error"] = "You are not logged in, please log in";
        header("Location: login.php");
        exit();
    }
}

function flashMessages()
{
    if (isset($_SESSION["error"])) {
        echo '<p class="flash" style="color: red;">' .
            htmlentities($_SESSION["error"]) .
            "</p>\n";
        unset($_SESSION["error"]);
    }
    if (isset($_SESSION["success"])) {
        echo '<p class="flash" style="color: green;">' .
            htmlentities($_SESSION["success"]) .
            "</p>\n";
        unset($_SESSION["success"]);
    }
}

# function to create tables, with some error checking
# To not repeatingly do this in the dbcreate.php
function tableCreate(string $query, SQLITE3 $db, string $name_table)
{
    $stmt = $db->prepare($query);
    $stmt->execute();
    if (!$stmt) {
        echo $db->lastErrorMsg();
    } else {
        echo "$name_table table created successfully\n";
    }
}
