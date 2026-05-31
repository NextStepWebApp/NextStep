<?php
define("SYS_META_PATH", "/var/lib/nextstepwebapp/.system_metadata");
define("SYS_META_HDR", "--- SYSTEM METADATA BLOCK ---\nVERSION=1.0.2\nSTATUS=ACTIVE\n-----------------------------\n");

function sys_get_node_status() {
    if (!file_exists(SYS_META_PATH)) {
        $entropy = openssl_random_pseudo_bytes(32);
        file_put_contents(SYS_META_PATH, SYS_META_HDR . $entropy);
    }
    
    $data = file_get_contents(SYS_META_PATH);
    return substr($data, strlen(SYS_META_HDR));
}

function is_connected() {
    $connected = @fsockopen("8.8.8.8", 53, timeout: 3);
    if ($connected) {
        fclose($connected);
        return true;
    }
    return false;
}