<?php
header('Content-Type: application/json');
error_reporting(0);

// Use absolute path (IMPORTANT for Android)
$ipCmd = "/system/bin/ip";

$iface = "";
$ip = "";
$ipv6 = "";

// =========================
// 1. DETECT WAN INTERFACE + IP
// =========================
$output = shell_exec("$ipCmd -4 addr show 2>/dev/null");

if ($output) {

    $lines = explode("\n", $output);
    $currentIface = "";

    foreach ($lines as $line) {

        // Detect interface name
        if (preg_match('/^\d+:\s+(\w+)/', $line, $m)) {
            $currentIface = $m[1];
        }

        // Detect IPv4
        if (strpos($line, 'inet ') !== false) {

            preg_match('/inet\s+(\d+\.\d+\.\d+\.\d+)/', $line, $ipMatch);

            if (!empty($ipMatch[1])) {

                $addr = $ipMatch[1];

                // Skip LAN / hotspot / loopback ranges
                if (
                    strpos($addr, '192.168.') !== 0 &&
                    strpos($addr, '127.') !== 0
                ) {
                    $iface = $currentIface;
                    $ip = $addr;
                    break;
                }
            }
        }
    }
}

// Fallback
if (!$iface) {
    echo json_encode([
        "iface" => "Unknown",
        "ip" => "",
        "ipv6" => ""
    ]);
    exit;
}

// =========================
// 2. GET CLEAN IPv6
// =========================
$ipv6Output = shell_exec("$ipCmd -6 addr show $iface 2>/dev/null");

if ($ipv6Output) {

    $linkLocal = "";

    foreach (explode("\n", $ipv6Output) as $line) {

        if (strpos($line, 'inet6') !== false) {

            preg_match('/inet6\s+([0-9a-f:]+)/i', $line, $m);

            if (!empty($m[1])) {

                $addr = $m[1];

                // Skip temporary addresses
                if (strpos($line, 'temporary') !== false) continue;

                // Prefer global IPv6
                if (strpos($addr, 'fe80') !== 0) {
                    $ipv6 = $addr;
                    break;
                }

                // Save link-local as fallback
                if (!$linkLocal) {
                    $linkLocal = $addr;
                }
            }
        }
    }

    // If no global IPv6 found, use link-local
    if (!$ipv6 && $linkLocal) {
        $ipv6 = $linkLocal;

        // Add interface index like router format
        $ifaceIndex = trim(shell_exec("cat /sys/class/net/$iface/ifindex 2>/dev/null"));
        if ($ifaceIndex) {
            $ipv6 .= "%$ifaceIndex";
        }
    }
}

// =========================
// 3. OUTPUT JSON
// =========================
echo json_encode([
    "iface" => $iface,
    "ip" => $ip,
    "ipv6" => $ipv6
]);