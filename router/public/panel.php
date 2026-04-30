<?php

$ip = $_SERVER['REMOTE_ADDR'];

$active_file = "/data/data/com.termux/files/home/router/active_users.txt";
$user_db     = __DIR__ . "/users_db.txt";

// ================= GET USER FROM ACTIVE =================
$username = null;
$plan = null;

if (file_exists($active_file)) {
    foreach (file($active_file) as $line) {
        $p = explode(":", trim($line));

        if ($p[0] == $ip) {
            $username = $p[1];
            break;
        }
    }
}

// ================= NOT AUTHENTICATED =================
if (!$username) {
    header("Location: index.php");
    exit;
}

// ================= LOAD PLAN =================
if (file_exists($user_db)) {
    foreach (file($user_db) as $u) {
        $p = explode(":", trim($u));

        if ($p[0] == $username) {
            $plan = [
                "speed"   => $p[2],
                "session" => $p[3],
                "data"    => $p[4]
            ];
            break;
        }
    }
}

// ================= GET MAC =================
$mac = "Unknown";
foreach (@file('/proc/net/arp') as $line) {
    if (strpos($line, $ip) !== false) {
        $p = preg_split('/\s+/', trim($line));
        $mac = $p[3];
    }
}
?>

<?php
function formatSpeed($speed){

    if(!$speed || $speed === "unlimited") return "Unlimited";

    $s = strtolower($speed);

    if(strpos($s, "mbit") !== false){
        return str_replace("mbit", " Mbps", $s);
    }

    if(strpos($s, "kbit") !== false){
        return str_replace("kbit", " Kbps", $s);
    }

    if(strpos($s, "gbit") !== false){
        return str_replace("gbit", " Gbps", $s);
    }

    return $speed; // fallback
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Panel</title>

<link rel="stylesheet" href="assets/fontawesome/css/all.min.css">

<style>
*{box-sizing:border-box;margin:0;padding:0}

body{
    font-family:'Segoe UI',system-ui;
    background: radial-gradient(circle at top,#1e293b,#020617);
    color:white;
}

/* container */
.container{
    padding:20px;
}

/* header */
.header{
    text-align:center;
    margin-bottom:10px;
}

/* ===== BIG GAUGE ===== */
.gauge-container{
    display:flex;
    justify-content:center;
    margin:25px 0;
}

.gauge{
    position:relative;
    width:280px;
    height:160px;
}

/* SVG arc */
.gauge svg{
    width:100%;
    height:100%;
}

/* icon (top center) */
.center-icon{
    position:absolute;
    top:50px;
    left:50%;
    transform:translateX(-50%);
    font-size:22px;
    color:#3b82f6;
    z-index:2;
}

/* value (bottom center) */
.gauge-value{
    position:absolute;
    bottom:5px;
    left:50%;
    transform:translateX(-50%);
    text-align:center;
    z-index:2;
}

.gauge-value .big{
    font-size:24px;
    font-weight:700;
}

.gauge-value .label{
    font-size:12px;
    color:#94a3b8;
}

/* ===== GRID ===== */
.grid{
    display:grid;
    grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
    gap:12px;
}

/* cards */
.card{
    background: rgba(17,24,39,0.7);
    backdrop-filter: blur(20px);
    border-radius:14px;
    padding:15px;
    border:1px solid rgba(255,255,255,0.08);
    box-shadow:0 10px 40px rgba(0,0,0,0.6);
}

/* titles */
.card-title{
    font-size:13px;
    color:#94a3b8;
}

/* values */
.value{
    font-size:16px;
    font-weight:600;
    margin-top:4px;
}

/* status */
.status-ok{color:#22c55e}
.status-bad{color:#ef4444}

/* logout */
.logout-box{
    margin-top:25px;
    display:flex;
    justify-content:center;
}

button{
    width:260px;
    padding:12px;
    border:none;
    border-radius:10px;
    background:linear-gradient(135deg,#ef4444,#dc2626);
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:scale(1.05);
    box-shadow:0 10px 25px rgba(239,68,68,0.4);
}
</style>

<div class="container">

<div class="header">
<h2><i class="fas fa-wifi"></i> Connected</h2>
</div>

<!-- ===== GAUGE ===== -->
<div class="gauge-container">
<div class="gauge">

<svg viewBox="0 0 100 60">
<!-- background -->
<path d="M10 50 A40 40 0 0 1 90 50"
 stroke="#334155" stroke-width="8" fill="none"/>

<!-- progress -->
<path id="gaugeFill"
 d="M10 50 A40 40 0 0 1 90 50"
 stroke="#3b82f6"
 stroke-width="8"
 fill="none"
 stroke-linecap="round"
 stroke-dasharray="126"
 stroke-dashoffset="126"/>
</svg>

<!-- icon -->
<div class="center-icon">
<i class="fas fa-database"></i>
</div>

<!-- value -->
<div class="gauge-value">
<div class="big" id="total">0 MB</div>
<div class="label">Data Used</div>
</div>

</div>
</div>

<!-- ===== GRID ===== -->
<div class="grid">

<div class="card">
<div class="card-title"><i class="fas fa-user"></i> User</div>
<div class="value"><?= htmlspecialchars($username) ?></div>
</div>

<div class="card">
<div class="card-title">IP</div>
<div class="value"><?= $ip ?></div>
</div>

<div class="card">
<div class="card-title">MAC</div>
<div class="value"><?= $mac ?></div>
</div>

<?php if($plan): ?>
<div class="card">
<div class="card-title">Speed</div>
<div class="value"><?= formatSpeed($plan['speed']) ?></div>
</div>

<div class="card">
<div class="card-title">Session</div>
<div class="value"><?= $plan['session'] ?> Minutes</div>
</div>

<div class="card">
<div class="card-title">Data Plan</div>
<div class="value"><?= $plan['data'] ?> MB</div>
</div>
<?php endif; ?>

<div class="card">
<div class="card-title"><i class="fas fa-clock"></i> Time</div>
<div class="value" id="time">0s</div>
</div>

<div class="card">
<div class="card-title"><i class="fas fa-tachometer-alt"></i> Speed</div>
<div class="value" id="speed">0 KB/s</div>
</div>

<div class="card">
<div class="card-title"><i class="fas fa-globe"></i> Internet</div>
<div class="value status-ok" id="status">Checking...</div>
</div>

</div>

<!-- LOGOUT -->
<div class="logout-box">
<form action="logout_user.php" method="post">
<button><i class="fas fa-power-off"></i> Disconnect</button>
</form>
</div>

</div>

<script>

// INTERNET
function checkInternet(){
fetch("https://www.google.com",{mode:"no-cors"})
.then(()=>{status.innerText="Connected";status.className="value status-ok"})
.catch(()=>{status.innerText="No Internet";status.className="value status-bad"})
}
setInterval(checkInternet,5000)

const status=document.getElementById("status")

// SPEED
let lastBytes=0

setInterval(()=>{
fetch("usage.php")
.then(r=>r.json())
.then(d=>{
let bytes=d.bytes||0
let speed=(bytes-lastBytes)/1024/2
if(speed<0) speed=0
lastBytes=bytes
document.getElementById("speed").innerText=speed.toFixed(2)+" KB/s"
})
},2000)

// DATA + TIME + GAUGE
setInterval(()=>{
fetch("get_usage.php")
.then(r=>r.json())
.then(d=>{

let data=d.data||0
let mb=data/1024/1024

document.getElementById("total").innerText=mb.toFixed(2)+" MB"

// gauge fill
let max=<?= ($plan && $plan['data']!="unlimited") ? $plan['data'] : 1024 ?>;
let percent=Math.min(mb/max,1)

let offset=126-(126*percent)
document.getElementById("gaugeFill").style.strokeDashoffset=offset

// time
let t=parseInt(d.time)||0
let h=Math.floor(t/3600)
let m=Math.floor((t%3600)/60)
let s=t%60

document.getElementById("time").innerText=h+"h "+m+"m "+s+"s"

})
},2000)

</script>
</body>
</html>