<?php
header('Content-Type: application/json');
error_reporting(0);

$iface = $_GET['iface'] ?? '';

if (!$iface) {
    echo json_encode(["rx"=>0,"tx"=>0]);
    exit;
}

$data = @file('/proc/net/dev');

$rx = 0;
$tx = 0;

if ($data) {
    foreach ($data as $line) {
        if (strpos($line, $iface . ":") !== false) {
            $parts = preg_split('/\s+/', trim($line));
            $rx = isset($parts[1]) ? (int)$parts[1] : 0;
            $tx = isset($parts[9]) ? (int)$parts[9] : 0;
        }
    }
}

echo json_encode([
    "rx"=>$rx,
    "tx"=>$tx
]);