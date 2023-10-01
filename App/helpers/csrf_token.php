<?php

function csrf_token($token = null) {
    if($token){
        // Check if is a valid token
        if($token !== $_COOKIE['csrf_token']){
            return false;
        }
        return true;
    }

    if(!is_null($token)){
        return false;
    }

    // Generate and set the token
    $token = bin2hex(openssl_random_pseudo_bytes(35));
    setcookie('csrf_token', $token, time() + 60 * 30, "/", "", false, true);
    return $token;
}