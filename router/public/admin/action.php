<?php
session_start();
if (!isset($_SESSION['admin'])) exit;

$ip = $_POST['ip'];

if (isset($_POST['allow'])) {
    shell_exec("su -c '/data/data/com.termux/files/home/router/allow_ip.sh $ip'");
}

if (isset($_POST['block'])) {
    shell_exec("su -c '/data/data/com.termux/files/home/router/block_ip.sh $ip'");
}

header("Location: index.php");
