<?php
$db_file = "mydatabase.db";
if (file_exists($db_file)) {
    unlink($db_file);
}

$db = new SQLite3("mydatabase.db");

if (!$db) {
    echo $db->lastErrorMsg();
} else {
    echo "Database created (or opened) successfully\n";
}

$sql = <<<EOF
      CREATE TABLE TEACHERS (
      teacher_id INTEGER PRIMARY KEY AUTOINCREMENT,
      teacher_email TEXT NOT NULL UNIQUE,
      teacher_name TEXT NOT NULL,
      teacher_password TEXT NOT NULL
      );

      INSERT INTO TEACHERS (teacher_email, teacher_name, teacher_password) VALUES ("admin@gmail.com", "Cardoze", "spaans123");
EOF;

$ret = $db->exec($sql);
if (!$ret) {
    echo $db->lastErrorMsg();
} else {
    echo "Table created successfully\n";
}
$db->close();
