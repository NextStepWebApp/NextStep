<?php

# These are the file path config
# This is the only place that is allowed to have a specific path besides the config
$nextstep_config_path = "/etc/nextstepwebapp/nextstep_config.json";

$nextstep_config = json_decode(file_get_contents($nextstep_config_path), true);

# This is location to the database
$db_file = $nextstep_config["database_file_path"];

# These are the configs for validations
$config_path = $nextstep_config["config_path"];
$config = json_decode(file_get_contents($config_path), true);

# This is the location to the branding json
$branding_path = $nextstep_config["branding_path"];
$branding = json_decode(file_get_contents($branding_path), true);

# Global color validate_teacher_name
# This is the theme that all the users will get by default
$color_theme_path = $nextstep_config["color_theme_path"];
$color_theme_system = json_decode(file_get_contents($color_theme_path), true);
