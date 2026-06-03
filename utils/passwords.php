<?php

function genPassword(int $length)
{
    $password = "";
    $letters = range("a", "z");
    for ($i = 0; $i < $length; $i++) {
        if ($i % 2 == 0) {
            $password .= rand(0, 9);
        } else {
            $index = rand(0, count($letters) - 1);
            $password .= $letters[$index];
        }
    }
    return $password;
}

function genVerificationCode(int $length)
{
    $code = "";
    for ($i = 0; $i < $length; $i++) {
        $code .= rand(0, 9);
    }
    return $code;
}