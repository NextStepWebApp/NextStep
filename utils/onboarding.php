<?php

function setup_checker()
{
    $setup_config_path = "/var/lib/nextstepwebapp/setup.json";
    $setup_config = json_decode(file_get_contents($setup_config_path), true);
    $value = $setup_config["setup_value"];
    if ($value === 0) {
        header("Location: /NextStep/setup/onboarding.php");
        exit();
    }
}
