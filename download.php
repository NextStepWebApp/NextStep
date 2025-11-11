<?php
require_once "utils.php";
session_start();
loginSecurity();
super_user_privilages($_SESSION["teacher_username"]);

if (!isset($_SESSION["new_teacher_credentials"]) || !isset($_SESSION["new_teacher_filename"])) {
    header("Location: create_teacher.php");
    exit();
}
$credentials_content = $_SESSION["new_teacher_credentials"];
$filename = $_SESSION["new_teacher_filename"];

unset($_SESSION["new_teacher_credentials"]);
unset($_SESSION["new_teacher_filename"]);

header('Content-Type: text/plain'); 
header('Content-Disposition: attachment; filename="' . $filename . '"'); 
header('Content-Length: ' . strlen($credentials_content)); 
header('Cache-Control: no-cache, no-store, must-revalidate'); 
header('Pragma: no-cache'); 
header('Expires: 0'); 
echo $credentials_content; 
exit();
?>
