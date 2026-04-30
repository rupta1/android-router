<?php

$ip = $_SERVER['REMOTE_ADDR'];

$active_file = "/data/data/com.termux/files/home/router/active_users.txt";
$usage_file  = __DIR__ . "/user_usage.txt";

// ================= GET USER FROM ACTIVE =================
$user = null;

if (file_exists($active_file)) {
    foreach (file($active_file) as $line) {
        $p = explode(":", trim($line));

        if ($p[0] == $ip) {
            $user = $p[1];
            break;
        }
    }
}

// ================= READ USAGE =================
$data = 0;
$time = 0;

if ($user && file_exists($usage_file)) {
    foreach (file($usage_file) as $l) {
        $p = explode(":", trim($l));

        if (count($p) >= 3 && $p[0] === $user) {
            $data = (int)$p[1];
            $time = (int)$p[2];
            break;
        }
    }
}

// ================= RESPONSE =================
header("Content-Type: application/json");

echo json_encode([
    "user" => $user,
    "data" => $data,
    "time" => $time
]);