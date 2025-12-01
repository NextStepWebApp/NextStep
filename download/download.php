<?php
require_once "../utils.php";
session_start();
$settings = download_page_settings(); # internal checking security

if ($settings == "teacher") {
    $credentials_content = $_SESSION["new_teacher_credentials"];
    $filename = $_SESSION["new_teacher_filename"];
    unset($_SESSION["new_teacher_credentials"]);
    unset($_SESSION["new_teacher_filename"]);
    header('content-type: text/plain'); 
    header('content-disposition: attachment; filename="' . $filename . '"'); 
    header('content-length: ' . strlen($credentials_content)); 
    header('cache-control: no-cache, no-store, must-revalidate'); 
    header('pragma: no-cache'); 
    header('expires: 0'); 
    echo $credentials_content; 
    exit();   
}

if ($settings == "student") {
    $csv_content = $_SESSION["export_csv_content"];
    $filename = $_SESSION["export_csv_filename"];
    unset($_SESSION["export_csv_content"]);
    unset($_SESSION["export_csv_filename"]);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($csv_content));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo $csv_content;
    exit();
}

if ($settings == "admin") {
    # Delete the setup folder
    #$config = json_decode(file_get_contents($nextstep_config_path), true); # $nextstep_config comes from utils
    #$dirname = $config["setup_path_dir"];
    #array_map('unlink', glob("$dirname/*.*"));
    #rmdir($dirname);
    
    # Download the admin credentials
    $admin_credentials = $_SESSION["new_admin_credentials"];
    $filename = $_SESSION["new_admin_filename"];
    session_destroy(); # There is still no login so just destroy not needed anymore
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($admin_credentials));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo $admin_credentials;
    exit();
}

header("Location: /NextStep/");
exit();
