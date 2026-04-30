<?php

// =========================
// HOTSPOT INFO (FULL)
// =========================
function getHotspotInfo(){

    $dump = shell_exec("su -c 'dumpsys wifi' 2>/dev/null");

    $data = [
        "ssid" => "Unknown",
        "band" => "Unknown",
        "frequency" => "",
        "bandwidth" => "",
        "max_clients" => "",
        "clients" => 0,
        "country" => "",
        "security" => "Unknown",
        "encryption" => "Unknown",
        "wps" => "Unknown"
    ];

    if(!$dump) return $data;

    // SSID
    if(preg_match('/ssid = \"([^\"]+)\"/', $dump, $m)){
        $data["ssid"] = $m[1];
    }

    // Country
    if(preg_match('/mSoftApCountryCode:\s*(\w+)/', $dump, $m)){
        $data["country"] = $m[1];
    }

    // Frequency + Band
    if(preg_match('/frequency=\s*(\d+)/', $dump, $m)){
        $freq = (int)$m[1];
        $data["frequency"] = $freq . " MHz";

        if($freq >= 2400 && $freq < 2500){
            $data["band"] = "2.4 GHz";
        } elseif($freq >= 4900){
            $data["band"] = "5 GHz";
        }
    }

    // Bandwidth
    if(preg_match('/bandwidth=\s*(\d+)/', $dump, $m)){
        $bwMap = [
            2 => "20 MHz",
            4 => "40 MHz",
            8 => "80 MHz",
        ];
        $data["bandwidth"] = $bwMap[(int)$m[1]] ?? $m[1];
    }

    // Max Clients
    if(preg_match('/MaximumSupportedClientNumber=(\d+)/', $dump, $m)){
        $data["max_clients"] = $m[1];
    }

    // Current Clients
    if(preg_match_all('/num_connected_clients=(\d+)/', $dump, $m)){
        $data["clients"] = max($m[1]);
    }

    // Security Type
    $secCode = -1;
    if(preg_match('/SecurityType\s*=\s*(\d+)/', $dump, $m)){
        $secCode = (int)$m[1];
    }

    $secMap = [
        0 => "Open",
        1 => "WPA2-PSK",
        2 => "WPA3-SAE",
        3 => "WPA2/WPA3 Mixed"
    ];

    $data["security"] = $secMap[$secCode] ?? "Unknown";

    // Encryption
    switch($secCode){
        case 0: $data["encryption"] = "None"; break;
        case 1: $data["encryption"] = "AES (CCMP)"; break;
        case 2: $data["encryption"] = "SAE (WPA3)"; break;
        case 3: $data["encryption"] = "AES + SAE"; break;
        default: $data["encryption"] = "Unknown";
    }

    // WPS
    if(preg_match('/wps\s*=\s*(\d+)/i', $dump, $m)){
        $data["wps"] = ($m[1] == 1) ? "Enabled" : "Disabled";
    } else {
        $data["wps"] = "Disabled"; // fallback (Android default)
    }

    return $data;
}


// =========================
// AP INTERFACE
// =========================
function getApIface(){
    foreach (['ap0','wlan1','p2p0'] as $i) {
        if (@file_exists("/sys/class/net/$i")) return $i;
    }
    return "ap0";
}


// =========================
// DHCP RANGE
// =========================
function getDhcpRange($iface){
    $out = shell_exec("/system/bin/ip -4 addr show $iface 2>/dev/null");

    if ($out && preg_match('/inet\s+(\d+\.\d+\.\d+)\.(\d+)/', $out, $m)) {
        return $m[1] . ".1 - " . $m[1] . ".254";
    }

    return "Unknown";
}


// =========================
// ROUTER IP
// =========================
function getGateway(){
    $iface = getApIface();

    $cmd = "/system/bin/ip -4 addr show $iface 2>/dev/null | grep inet | awk '{print \$2}' | cut -d/ -f1";
    $ip = trim(shell_exec($cmd));

    return $ip ?: "Unknown";
}

// =========================
// ACTIVE CLIENTS
// =========================
function getClients(){

    $arp = @file('/proc/net/arp');
    $clients = [];

    foreach ($arp as $i => $line) {
        if ($i == 0) continue;

        $p = preg_split('/\s+/', trim($line));

        if (count($p) >= 4) {

            $ip  = $p[0];
            $mac = $p[3];

            if ($mac == "00:00:00:00:00:00") continue;

            $alive = shell_exec("ping -c 1 -W 1 $ip 2>/dev/null");

            if ($alive) {
                $clients[] = [
                    'ip' => $ip,
                    'mac' => $mac
                ];
            }
        }
    }

    return $clients;
}


// =========================
// INIT
// =========================
$hotspot = getHotspotInfo();
$iface   = getApIface();
$gateway = getGateway();
$dhcp    = getDhcpRange($iface);
$clients = getClients();

?>

<h2 class="title"><i class="fas fa-network-wired"></i> LAN Dashboard</h2>

<style>

/* ===== GRID ===== */
.lan-grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(220px,1fr));
    gap:15px;
}

/* ===== CARD ===== */
.card{
    background: rgba(17,24,39,0.7);
    backdrop-filter: blur(20px);

    border-radius:14px;
    padding:15px;

    border:1px solid rgba(255,255,255,0.08);
    box-shadow:0 10px 40px rgba(0,0,0,0.6);

    transition:0.25s;
}

.card:hover{
    transform:translateY(-5px);
}

/* ===== TITLE ===== */
.title{
    margin-bottom:15px;
}

/* ===== LABEL / VALUE ===== */
.label{
    font-size:12px;
    color:#94a3b8;
}

.value{
    font-size:16px;
    font-weight:600;
}

/* ===== BIG CARD ===== */
.main-card{
    grid-column: span 2;
}

/* ===== DEVICE GRID ===== */
.device-grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(250px,1fr));
    gap:15px;
}

/* ===== DEVICE CARD ===== */
.device{
    background: rgba(17,24,39,0.6);
    border-radius:12px;
    padding:15px;

    border:1px solid rgba(255,255,255,0.08);
    transition:0.25s;
}

.device:hover{
    transform:scale(1.02);
}

/* ===== BUTTONS ===== */
.btn{
    padding:6px 12px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    margin-right:5px;
}

.allow{ background:#22c55e; color:white; }
.block{ background:#ef4444; color:white; }

.btn:hover{
    opacity:0.85;
}

/* ===== STATUS BADGE ===== */
.badge{
    padding:3px 8px;
    border-radius:6px;
    font-size:12px;
}

.ok{ background:#064e3b; color:#22c55e; }
.warn{ background:#7c2d12; color:#f97316; }

/* ===== ANIMATION ===== */
@keyframes fadeIn{
    from{opacity:0; transform:translateY(10px)}
    to{opacity:1; transform:translateY(0)}
}

.card, .device{
    animation:fadeIn 0.4s ease;
}

</style>

<!-- ===== HOTSPOT INFO ===== -->
<div class="lan-grid">

<div class="card main-card">
<div class="label">SSID</div>
<div class="value"><?= $hotspot['ssid'] ?></div>

<br>

<div class="label">Security</div>
<div class="value"><?= $hotspot['security'] ?> (<?= $hotspot['encryption'] ?>)</div>

<br>

<div class="label">Band</div>
<div class="value"><?= $hotspot['band'] ?> / <?= $hotspot['frequency'] ?></div>
</div>

<div class="card">
<div class="label">Channel Width</div>
<div class="value"><?= $hotspot['bandwidth'] ?></div>
</div>

<div class="card">
<div class="label">Country</div>
<div class="value"><?= $hotspot['country'] ?></div>
</div>

<div class="card">
<div class="label">WPS</div>
<div class="value"><?= $hotspot['wps'] ?></div>
</div>

<div class="card">
<div class="label">Router IP</div>
<div class="value"><?= $gateway ?></div>
</div>

<div class="card">
<div class="label">DHCP Range</div>
<div class="value"><?= $dhcp ?></div>
</div>

<div class="card">
<div class="label">Clients</div>
<div class="value">
<span class="badge ok"><?= $hotspot['clients'] ?></span>
 / <?= $hotspot['max_clients'] ?>
</div>
</div>

</div>

<!-- ===== CONNECTED DEVICES ===== -->
<h3 style="margin-top:20px;"><i class="fas fa-laptop"></i> Connected Devices</h3>

<div class="device-grid">

<?php if(empty($clients)): ?>

<div class="card">
<div class="label">No active devices</div>
</div>

<?php else: ?>

<?php foreach($clients as $c): ?>

<div class="device">

<div class="label">IP</div>
<div class="value"><?= $c['ip'] ?></div>

<div class="label">MAC</div>
<div class="value"><?= $c['mac'] ?></div>

<br>

<form method="post" action="action.php">
<input type="hidden" name="ip" value="<?= $c['ip'] ?>">

<button class="btn allow" name="allow">
<i class="fas fa-check"></i> Allow
</button>

<button class="btn block" name="block">
<i class="fas fa-ban"></i> Block
</button>
</form>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>