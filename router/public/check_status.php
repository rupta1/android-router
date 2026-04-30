<?php
$ip = $_GET['ip'];

$output = shell_exec("su -c 'iptables -L FORWARD -n'");

if (strpos($output, $ip) !== false) {
    echo "allowed";
} else {
    echo "blocked";
}
