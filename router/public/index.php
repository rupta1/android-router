<?php
session_start();

$ip = $_SERVER['REMOTE_ADDR'];

$auth_file = __DIR__ . '/data/auth.txt';
$user_db   = __DIR__ . '/users_db.txt';
$usage_file= __DIR__ . '/user_usage.txt';

// ================= LOAD USERS =================
$users = file($user_db);

// ================= LOAD USAGE =================
$usage_data = file_exists($usage_file) ? file($usage_file) : [];

function getUsage($user, $usage_data){
    foreach($usage_data as $u){
        $p = explode(":", trim($u));
        if($p[0] === $user){
            return [
                "data" => (int)$p[1],
                "time" => (int)$p[2]
            ];
        }
    }
    return ["data"=>0,"time"=>0];
}

// ================= CHECK ALREADY ACTIVE (NEW LOGIC) =================
$active_file = "/data/data/com.termux/files/home/router/active_users.txt";
$already_active = false;

if (file_exists($active_file)) {
    foreach (file($active_file) as $line) {
        $p = explode(":", trim($line));
        if ($p[0] == $ip) {
            $already_active = true;
            break;
        }
    }
}

// ================= POST LOGIN =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user = $_POST['user'];
    $pass = $_POST['pass'];

    $valid = false;
    $plan  = [];

    foreach ($users as $u) {
        $p = explode(":", trim($u));

        if ($p[0] === $user && $p[1] === $pass) {
            $valid = true;
            $plan = [
                "speed"=>$p[2],
                "session"=>$p[3],
                "data"=>$p[4]
            ];
            break;
        }
    }

    if ($valid) {

        $usage = getUsage($user, $usage_data);

        // ================= DATA LIMIT =================
        if($plan['data'] !== "unlimited"){
            $limit = $plan['data'] * 1024 * 1024;
            if($usage['data'] >= $limit){
                $err = "Data quota exhausted";
                goto END;
            }
        }

        // ================= TIME LIMIT =================
        if($plan['session'] !== "unlimited"){
            if($usage['time'] >= ($plan['session'] * 60)){
                $err = "Session time exhausted";
                goto END;
            }
        }

        // ================= ALLOW NETWORK =================
        shell_exec("su -c '/data/data/com.termux/files/home/router/allow_ip.sh $ip {$plan['speed']}'");

        // ================= ACTIVE USERS (FIXED) =================
        // remove ONLY same IP
        if (file_exists($active_file)) {
            $lines = file($active_file);
            $new = "";

            foreach ($lines as $l) {
                $p = explode(":", trim($l));
                if ($p[0] != $ip) {
                    $new .= $l;
                }
            }

            file_put_contents($active_file, $new);
        }

        // append (multi-device safe)
        file_put_contents(
            $active_file,
            "$ip:$user:{$plan['data']}\n",
            FILE_APPEND
        );

        // ================= CLEAN SESSION FILES =================
        shell_exec("rm -f /data/data/com.termux/files/home/router/public/tmp_$ip.txt");
        shell_exec("rm -f /data/data/com.termux/files/home/router/public/time_$ip.txt");

        // ================= OPTIONAL SESSION (NOT USED BY SYSTEM) =================
        $_SESSION['username'] = $user;

        header("Location: panel.php");
        exit;

    } else {
        $err = "Invalid username or password";
    }
}

// ================= REDIRECT IF ACTIVE =================
if ($already_active) {
    header("Location: panel.php");
    exit;
}

END:
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>WiFi Login</title>

<link rel="stylesheet" href="assets/fontawesome/css/all.min.css">

<style>
*{box-sizing:border-box;margin:0;padding:0}

body{
    font-family: 'Segoe UI',system-ui;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: radial-gradient(circle at top,#1e293b,#020617);
    overflow:hidden;
    color:white;
}

/* animated background glow */
body::before{
    content:"";
    position:absolute;
    width:600px;
    height:600px;
    background: radial-gradient(circle,#2563eb55,transparent);
    filter: blur(100px);
    animation: float 8s infinite ease-in-out;
}

@keyframes float{
    0%,100%{transform:translateY(0)}
    50%{transform:translateY(-40px)}
}

/* card */
.box{
    position:relative;
    z-index:2;
    width:340px;
    padding:35px 25px;
    border-radius:18px;
    background: rgba(17,24,39,0.7);
    backdrop-filter: blur(20px);
    border:1px solid rgba(255,255,255,0.08);
    box-shadow:0 15px 50px rgba(0,0,0,0.6);
    text-align:center;
    animation: fade 0.6s ease;
}

@keyframes fade{
    from{opacity:0;transform:translateY(30px)}
    to{opacity:1;transform:translateY(0)}
}

/* logo */
.logo{
    font-size:42px;
    margin-bottom:10px;
    color:#3b82f6;
}

.title{
    font-size:20px;
    margin-bottom:20px;
    font-weight:600;
}

/* input group */
.input-group{
    position:relative;
    margin-bottom:14px;
}

.input-group i{
    position:absolute;
    top:50%;
    left:12px;
    transform:translateY(-50%);
    color:#94a3b8;
}

input{
    width:100%;
    padding:11px 12px 11px 38px;
    border:none;
    border-radius:10px;
    background:#020617;
    color:white;
    outline:none;
    transition:0.3s;
}

input:focus{
    box-shadow:0 0 0 2px #2563eb;
}

/* button */
button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background: linear-gradient(135deg,#2563eb,#1d4ed8);
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 25px rgba(37,99,235,0.4);
}

/* loader */
.loader{
    display:none;
    margin-top:15px;
}

.loader i{
    font-size:20px;
    animation: spin 1s linear infinite;
}

@keyframes spin{
    to{transform:rotate(360deg)}
}

/* error */
.error{
    margin-top:10px;
    color:#ef4444;
    font-size:14px;
}

/* footer */
.footer{
    margin-top:15px;
    font-size:12px;
    color:#64748b;
}
</style>
</head>

<body>

<div class="box">

<div class="logo">
    <i class="fas fa-wifi"></i>
</div>

<div class="title">Secure WiFi Access</div>

<form method="post" onsubmit="showLoader()">

<div class="input-group">
<i class="fas fa-user"></i>
<input name="user" placeholder="Username" required>
</div>

<div class="input-group">
<i class="fas fa-lock"></i>
<input name="pass" type="password" placeholder="Password" required>
</div>

<button type="submit">
<i class="fas fa-right-to-bracket"></i> Connect
</button>

</form>

<div class="loader" id="loader">
<i class="fas fa-spinner"></i>
<div style="margin-top:5px">Connecting...</div>
</div>

<div class="error"><?= $err ?? '' ?></div>

<div class="footer">
<i class="fas fa-shield-alt"></i> Secure Network
</div>

</div>

<script>

function showLoader(){
    document.getElementById("loader").style.display="block";
}

// smooth input glow UX
document.querySelectorAll("input").forEach(i=>{
    i.addEventListener("focus",()=>i.parentElement.style.transform="scale(1.02)");
    i.addEventListener("blur",()=>i.parentElement.style.transform="scale(1)");
});

</script>

</body>
</html>