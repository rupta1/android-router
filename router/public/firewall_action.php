<?php
header('Content-Type: application/json');
error_reporting(0);

$file = __DIR__ . "/firewall_store.txt";

// ---------- STORE ----------
function saveRule($type, $value){
    global $file;
    file_put_contents($file, "$type|$value\n", FILE_APPEND);
}

function removeRule($type, $value){
    global $file;

    $lines = @file($file, FILE_IGNORE_NEW_LINES);
    $new = [];

    foreach($lines as $l){
        if(trim($l) != "$type|$value"){
            $new[] = $l;
        }
    }

    file_put_contents($file, implode("\n", $new));
}

// ---------- CHAIN ----------
function chain($ip){
    return "USER_" . str_replace(".", "_", $ip);
}

function createChain($ip){
    $c = chain($ip);

    shell_exec("su -c 'iptables -N $c 2>/dev/null'");
    shell_exec("su -c 'iptables -D FORWARD -s $ip -j $c 2>/dev/null'");
    shell_exec("su -c 'iptables -I FORWARD -s $ip -j $c'");
}

$action = $_GET['action'] ?? '';

switch($action){

// ================= MAC (UNCHANGED) =================
case "block_mac":
    $mac = $_GET['mac'];
    shell_exec("su -c 'iptables -I FORWARD -m mac --mac-source $mac -j DROP'");
    saveRule("mac", $mac);
    echo json_encode(["status"=>"ok"]);
break;

case "remove_mac":
    $mac = $_GET['mac'];
    shell_exec("su -c 'iptables -D FORWARD -m mac --mac-source $mac -j DROP'");
    removeRule("mac", $mac);
    echo json_encode(["status"=>"ok"]);
break;


// ================= URL (PER DEVICE) =================
case "block_url":

    $ip = $_GET['ip'];
    $url = $_GET['url'];

    $c = chain($ip);
    createChain($ip);

    $ips = gethostbynamel($url);

    if($ips){
        foreach($ips as $dip){
            shell_exec("su -c 'iptables -I $c -d $dip -j DROP'");
        }
    }

    saveRule("url", "$ip|$url");

    echo json_encode(["status"=>"ok"]);
break;


case "remove_url":

    $ip = $_GET['ip'];
    $url = $_GET['url'];

    $c = chain($ip);

    $ips = gethostbynamel($url);

    if($ips){
        foreach($ips as $dip){
            shell_exec("su -c 'iptables -D $c -d $dip -j DROP 2>/dev/null'");
        }
    }

    removeRule("url", "$ip|$url");

    echo json_encode(["status"=>"ok"]);
break;


// ================= PROXY (UNCHANGED) =================
case "proxy_on":
    $ip = $_GET['ip'];
    $port = $_GET['port'];

    shell_exec("su -c 'iptables -t nat -A PREROUTING -p tcp -j DNAT --to $ip:$port'");
    saveRule("proxy", "$ip:$port");

    echo json_encode(["status"=>"ok"]);
break;

case "proxy_off":
    shell_exec("su -c 'iptables -t nat -F'");
    removeRule("proxy", "");
    echo json_encode(["status"=>"ok"]);
break;


// ================= LIST =================
case "list":

    $rules = @file($file, FILE_IGNORE_NEW_LINES);
    $data = ["mac"=>[], "url"=>[], "proxy"=>""];

    foreach($rules as $r){

        $parts = explode("|",$r);

        if($parts[0]=="mac"){
            $data["mac"][] = $parts[1];
        }

        if($parts[0]=="url"){
            $data["url"][] = [
                "ip"=>$parts[1],
                "url"=>$parts[2]
            ];
        }

        if($parts[0]=="proxy"){
            $data["proxy"] = $parts[1];
        }
    }

    echo json_encode($data);
break;

}