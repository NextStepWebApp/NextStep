<?php
require_once "utils.php";

$db_file = "nextstep_data.db";
if (file_exists($db_file)) {
    unlink($db_file);
}

$db = new SQLite3($db_file);

if (!$db) {
    echo $db->lastErrorMsg();
} else {
    echo "Database created (or opened) successfully\n";
}

$query = <<<EOF
      CREATE TABLE TEACHERS (
      teacher_id INTEGER PRIMARY KEY AUTOINCREMENT,
      teacher_email TEXT NOT NULL UNIQUE,
      teacher_name TEXT NOT NULL,
      teacher_username TEXT NOT NULL,
      teacher_password TEXT NOT NULL
      );

EOF;

$stmt = $db->prepare($query);
$stmt->execute();
if (!$stmt) {
    echo $db->lastErrorMsg();
} else {
    echo "Table created successfully\n";
}

$password = genPassword(8);

$query =
    "INSERT INTO TEACHERS (teacher_email, teacher_name, teacher_username, teacher_password) VALUES (:email, :name, :username, :password)";
$stmt = $db->prepare($query);
$stmt->bindValue(":email", "admin@admin.com", SQLITE3_TEXT);
$stmt->bindValue(":name", "ADMIN", SQLITE3_TEXT);
$stmt->bindValue(":username", "ADMIN", SQLITE3_TEXT);
$stmt->bindValue(":password", $password, SQLITE3_TEXT);

$stmt->execute();

if (!$stmt) {
    echo $db->lastErrorMsg();
} else {
    echo "Table created successfully\n";
    $location = "/home/william";
    $text = "hello world";
    savefile($location, "ADMIN-password.txt", $password);
}

$db->close();
