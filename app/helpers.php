<?php

function get_local_time($ip){

    $url = 'http://ip-api.com/json/'.$ip;

    $tz = file_get_contents($url);

    $tz = json_decode($tz,true)['timezone'];

    return $tz;

}

function get_ip_info($ip){

    $url = 'http://ip-api.com/json/178.178.85.202';

    $tz = file_get_contents($url);

    $tz = json_decode($tz,true);

    return $tz;

}

function usdt_helper($v, $code){

    if($code === 'USDT'){
        return number_format($v, 2, '.', '');
    }
    return number_format($v, 8, '.', '');
}

function set_format($v){
    return number_format($v, 10, '.', '');
}
