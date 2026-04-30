<?php
$file = "/data/data/com.termux/files/home/router/lan_config.txt";

$data = [
    "ssid" => "",
    "pass" => "",
    "sec"  => "wpa2",
    "band" => "any"
];

if(file_exists($file)){
    foreach(file($file) as $line){
        $p = explode("=", trim($line));
        if(count($p) == 2){
            $data[strtolower($p[0])] = $p[1];
        }
    }
}

echo json_encode($data);