<?php
function getWanInterface() {
    $routes = @shell_exec("ip route 2>/dev/null");

    if (!$routes) return "Unknown";

    foreach (explode("\n", $routes) as $line) {
        if (strpos($line, 'default') !== false) {
            if (preg_match('/dev\s+(\w+)/', $line, $m)) {
                return $m[1];
            }
        }
    }

    return "Unknown";
}

$iface = getWanInterface();
?>

<h2 class="title"><i class="fas fa-globe"></i> WAN Dashboard</h2>

<style>

/* ===== GRID LAYOUT ===== */
.wan-wrap{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap:15px;
}

/* ===== MAIN CARD ===== */
.main-card{
    grid-column: span 2;
    display:flex;
    justify-content:space-between;
    align-items:center;

    background: rgba(17,24,39,0.7);
    backdrop-filter: blur(20px);
    border-radius:14px;
    padding:18px;

    border:1px solid rgba(255,255,255,0.08);
    box-shadow:0 10px 40px rgba(0,0,0,0.6);
}

/* left info */
.main-left{
    display:flex;
    flex-direction:column;
    gap:6px;
}

.label{
    font-size:12px;
    color:#94a3b8;
}

.value{
    font-size:16px;
    font-weight:600;
}

/* status */
.status-ok{ color:#22c55e; }
.status-bad{ color:#ef4444; }

/* ===== SPEED BOX ===== */
.speed-box{
    display:flex;
    gap:20px;
}

.speed{
    text-align:center;
}

.speed i{
    font-size:18px;
    margin-bottom:4px;
}

.speed .val{
    font-size:18px;
    font-weight:700;
}

/* ===== SMALL CARDS ===== */
.card{
    background: rgba(17,24,39,0.7);
    backdrop-filter: blur(20px);

    border-radius:14px;
    padding:15px;

    border:1px solid rgba(255,255,255,0.08);
    box-shadow:0 10px 40px rgba(0,0,0,0.6);
}

/* ===== GRAPH ===== */
.graph-card{
    grid-column: span 2;
}

canvas{
    width:100%;
    height:200px;
}

/* animation */
@keyframes fadeIn{
    from{opacity:0; transform:translateY(10px)}
    to{opacity:1; transform:translateY(0)}
}

.card, .main-card{
    animation:fadeIn 0.4s ease;
}

</style>

<div class="wan-wrap">

<!-- ===== MAIN STATUS ===== -->
<div class="main-card">

<div class="main-left">
<div class="label">Interface</div>
<div class="value" id="iface">Loading...</div>

<div class="label">WAN IP</div>
<div class="value" id="ip">Loading...</div>

<div class="label">IPv6</div>
<div class="value" id="ipv6">Loading...</div>

<div class="label">Status</div>
<div class="value" id="status">Checking...</div>
</div>

<div class="speed-box">

<div class="speed">
<i class="fas fa-download"></i>
<div class="val" id="rx">0 KB/s</div>
</div>

<div class="speed">
<i class="fas fa-upload"></i>
<div class="val" id="tx">0 KB/s</div>
</div>

</div>

</div>

<!-- ===== TOTAL ===== -->
<div class="card">
<div class="label">Total Download</div>
<div class="value" id="trx">0 MB</div>
</div>

<div class="card">
<div class="label">Total Upload</div>
<div class="value" id="ttx">0 MB</div>
</div>

<!-- ===== GRAPH ===== -->
<div class="card graph-card">
<div class="label"><i class="fas fa-chart-line"></i> Live Bandwidth</div>
<canvas id="wanChart"></canvas>
</div>

</div>