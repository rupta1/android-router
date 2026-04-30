<?php

$ip = $_SERVER['REMOTE_ADDR'];
$chain = "USER_" . str_replace(".", "_", $ip);

// ================= GET IPTABLES =================
$output = shell_exec("su -c 'iptables -L $chain -v -x 2>/dev/null'");

$bytes = 0;

if ($output) {
    foreach (explode("\n", $output) as $line) {
        if (preg_match('/^\s*\d+\s+(\d+)/', $line, $m)) {
            $bytes += (int)$m[1];
        }
    }
}

// ================= RESPONSE =================
header('Content-Type: application/json');

echo json_encode([
    "ip"    => $ip,
    "bytes" => $bytes
]);