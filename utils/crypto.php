<?php
define("CRYPTO_CIPHER", "aes-256-gcm");

function crypto_encrypt($plaintext, $node_entropy) {
    $ivLength = openssl_cipher_iv_length(CRYPTO_CIPHER);
    $iv = openssl_random_pseudo_bytes($ivLength);
    
    $ciphertextRaw = openssl_encrypt($plaintext, CRYPTO_CIPHER, $node_entropy, OPENSSL_RAW_DATA, $iv, $tag);
    $ciphertext = base64_encode($iv) . ":" . base64_encode($tag) . ":" . base64_encode($ciphertextRaw);
    
    return $ciphertext;
}

function crypto_decrypt($encryptedData, $node_entropy) {
    $parts = explode(':', $encryptedData);
    if (count($parts) !== 3) {
        return false; 
    }
    
    $iv = base64_decode($parts[0]);
    $tag = base64_decode($parts[1]);
    $ciphertextRaw = base64_decode($parts[2]);
    $ciphertext = openssl_decrypt($ciphertextRaw, CRYPTO_CIPHER, $node_entropy, OPENSSL_RAW_DATA, $iv, $tag);

    return $ciphertext;
}

