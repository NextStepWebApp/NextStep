<?php
# function that is used for the download pages to see what for type of download is asked
function download_page_settings()
{
    $page_settings = ["teacher", "student", "admin"];

    if (
        isset($_SESSION["new_teacher_credentials"]) ||
        isset($_SESSION["new_teacher_filename"])
    ) {
        # Teacher download requires login
        loginSecurity();
        //super_user_privilages($_SESSION["teacher_username"]);
        return $page_settings[0];
    } elseif (
        isset($_SESSION["export_csv_content"]) ||
        isset($_SESSION["export_csv_filename"])
    ) {
        # Student export requires login
        loginSecurity();
        //super_user_privilages($_SESSION["teacher_username"]);
        return $page_settings[1];
    } elseif (
        isset($_SESSION["new_admin_credentials"]) ||
        isset($_SESSION["new_admin_filename"])
    ) {
        # Admin download during onboarding - no login required
        return $page_settings[2];
    } else {
        header("Location: /NextStep/");
        exit();
    }
}
