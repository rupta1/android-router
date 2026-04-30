<?php
error_reporting(0);
header('Content-Type: application/json');


// ================= CPU USAGE =================
function getCPU(){

    $stat1 = file('/proc/stat');
    $cpu1 = preg_split('/\s+/', trim($stat1[0]));

    $total1 = $cpu1[1]+$cpu1[2]+$cpu1[3]+$cpu1[4];
    $idle1  = $cpu1[4];

    usleep(300000); // 0.3 sec delay

    $stat2 = file('/proc/stat');
    $cpu2 = preg_split('/\s+/', trim($stat2[0]));

    $total2 = $cpu2[1]+$cpu2[2]+$cpu2[3]+$cpu2[4];
    $idle2  = $cpu2[4];

    $total = $total2 - $total1;
    $idle  = $idle2 - $idle1;

    if($total == 0) return 0;

    return round((1 - ($idle / $total)) * 100);
}


// ================= RAM =================
function getRAM(){

    $mem = file('/proc/meminfo');

    $total = 0;
    $free = 0;

    foreach($mem as $line){

        if(strpos($line, 'MemTotal') !== false){
            $total = (int)filter_var($line, FILTER_SANITIZE_NUMBER_INT);
        }

        if(strpos($line, 'MemAvailable') !== false){
            $free = (int)filter_var($line, FILTER_SANITIZE_NUMBER_INT);
        }
    }

    if($total == 0) return 0;

    $used = $total - $free;

    return round(($used / $total) * 100);
}


// ================= UPTIME =================
function getUptime(){

    $uptime = file_get_contents('/proc/uptime');
    $seconds = (int)explode(" ", $uptime)[0];

    $d = floor($seconds / 86400);
    $h = floor(($seconds % 86400) / 3600);
    $m = floor(($seconds % 3600) / 60);

    return ($d>0?$d."d ":"") . ($h>0?$h."h ":"") . $m."m";
}


// ================= RESPONSE =================
echo json_encode([
    "cpu" => getCPU(),
    "ram" => getRAM(),
    "uptime" => getUptime()
]);