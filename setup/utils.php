<?php
# This is the utils file for the setup part

# This is the only place that is allowed to have a specific path besides the config
$nextstep_config = "/etc/nextstepwebapp/nextstep_config.json";

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


# function to create tables, with some error checking
# To not repeatingly do this in the dbcreate.php
/*function tableCreate(string $query, SQLite3 $db, string $name_table)
{
   $stmt = $db->prepare($query);
    $result = $stmt->execute();
    if (!$result) {
        die("Error creating $name_table: " . $db->lastErrorMsg() . "\n");
        $db->close();
    } else {
        echo "$name_table table created successfully\n";
    }
}*/
function tableCreate(string $query, SQLite3 $db, string $name_table)
{
    $stmt = $db->prepare($query);

    if (!$stmt) {
        error_log("Error preparing query for $name_table: " .
            $db->lastErrorMsg() .
            "\n");
    }

    $result = $stmt->execute();

    if (!$result) {
        error_log("Error creating $name_table: " . $db->lastErrorMsg() . "\n");
    } else {
        error_log("$name_table table created successfully\n");
    }
}
