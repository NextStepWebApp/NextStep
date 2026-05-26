<?php

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

function errorMessages(string $message, string $details)
{
    error_log("$message: $details");
    $_SESSION["error"] = $message;
    header("Location: /NextStep/failure.php");
    exit();
}
