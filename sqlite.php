<?php

$db_file = "nextstep_data.db";

$db = new SQLite3($db_file);

if (!$db) {
    die("Failed to open DB: " . $db->lastErrorMsg());
}
