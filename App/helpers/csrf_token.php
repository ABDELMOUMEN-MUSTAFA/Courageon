<?php

function csrf_token($token = null) {
    if($token){
        // Check if the giving token is valid
        if($token !== session('csrf_token')->get()){
            return false;
        }        
        return true;
    }

    // the string "lowhrs569874" appended to session_id because if the attacker knows the session id he can guess the csrf token by passing it to sha1() function
    $token = sha1(session_id() . "lowhrs569874");

    session('csrf_token')->set($token);
    return $token;
}