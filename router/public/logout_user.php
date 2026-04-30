<?php
session_start();

$ip = $_SERVER['REMOTE_ADDR'];
$user = $_SESSION['username'] ?? null;
$start = $_SESSION['start'] ?? time();

$usage_file = __DIR__ . '/user_usage.txt';
$auth_file  = __DIR__ . '/data/auth.txt';
$active_file= "/data/data/com.termux/files/home/router/active_users.txt";

// ================= SAVE SESSION TIME =================
if($user && file_exists($usage_file)){

    $used = time() - $start;

    $lines = file($usage_file);
    $new = "";

    $found = false;

    foreach($lines as $l){
        $p = explode(":", trim($l));

        if($p[0] == $user){

            $new_time = $p[2] + $used;
            $new .= "$user:{$p[1]}:$new_time\n";
            $found = true;

        } else {
            $new .= $l;
        }
    }

    // if user not exists yet
    if(!$found){
        $new .= "$user:0:$used\n";
    }

    file_put_contents($usage_file, $new);
}

// ================= REMOVE FROM AUTH =================
if(file_exists($auth_file)){
    $lines = file($auth_file);
    $new = "";

    foreach($lines as $l){
        if(trim($l) !== $ip){
            $new .= $l;
        }
    }

    file_put_contents($auth_file, $new);
}

// ================= REMOVE FROM ACTIVE USERS =================
if(file_exists($active_file)){
    $lines = file($active_file);
    $new = "";

    foreach($lines as $l){
        if(strpos($l, "$ip:") !== 0){
            $new .= $l;
        }
    }

    file_put_contents($active_file, $new);
}

shell_exec("sh /data/data/com.termux/files/home/router/monitor_usage.sh once");

// ================= BLOCK USER =================
shell_exec("su -c '/data/data/com.termux/files/home/router/block_ip.sh $ip'");

// ================= DESTROY SESSION =================
session_unset();
session_destroy();

// ================= REDIRECT =================
header("Location: index.php");
exit;