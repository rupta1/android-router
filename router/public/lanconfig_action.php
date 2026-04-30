<?php
header('Content-Type: application/json');

$config_file = "/data/data/com.termux/files/home/router/lan_config.txt";

$ssid = $_GET['ssid'] ?? '';
$pass = $_GET['pass'] ?? '';
$sec  = $_GET['sec']  ?? 'wpa2';
$band = $_GET['band'] ?? 'any';

if(!$ssid){
    echo json_encode(["msg"=>"SSID required"]);
    exit;
}

// ================= SAVE CONFIG =================
file_put_contents($config_file,
    "SSID=$ssid\nPASS=$pass\nSEC=$sec\nBAND=$band"
);

// ================= APPLY CONFIG =================

// sanitize for shell
$ssid_safe = escapeshellarg($ssid);
$pass_safe = escapeshellarg($pass);

// stop hotspot
shell_exec("su -c 'cmd wifi stop-softap'");
sleep(2);

// ---------- BAND ----------
if($band == "2"){
    shell_exec("su -c 'cmd wifi force-softap-band enabled 2'");
}elseif($band == "5"){
    shell_exec("su -c 'cmd wifi force-softap-band enabled 5'");
}else{
    shell_exec("su -c 'cmd wifi force-softap-band disabled'");
}

// ---------- SECURITY ----------
if($sec == "open"){
    $cmd = "cmd wifi start-softap $ssid_safe open";
}else{
    if($sec == "wpa3"){
        $sec = "wpa2";
    }
    $cmd = "cmd wifi start-softap $ssid_safe $sec $pass_safe";
}

// start hotspot
shell_exec("su -c '$cmd'");

echo json_encode(["msg"=>"Hotspot saved & applied successfully"]);