<?php
header('Content-Type: application/json');

$file = "/data/data/com.termux/files/home/router/public/users_db.txt";
$usage_file = "/data/data/com.termux/files/home/router/public/user_usage.txt";
$active_file = "/data/data/com.termux/files/home/router/active_users.txt";

$action = $_GET['action'] ?? '';

function readUsers($file){
    if(!file_exists($file)) return [];
    $lines = file($file);
    $users = [];

    foreach($lines as $l){
        $p = explode(":", trim($l));
        if(count($p) >= 5){
            $users[] = [
                "user"=>$p[0],
                "pass"=>$p[1],
                "speed"=>$p[2],
                "session"=>$p[3],
                "data"=>$p[4]
            ];
        }
    }
    return $users;
}

switch($action){

// ================= LIST USERS =================
case "list":
    echo json_encode(readUsers($file));
break;


// ================= CREATE USER =================
case "create":

    $u = $_GET['u'] ?? '';
    $p = $_GET['p'] ?? '';
    $s = $_GET['s'] ?? 'unlimited';
    $t = $_GET['t'] ?? 'unlimited';
    $d = $_GET['d'] ?? 'unlimited';

    if(!$u || !$p){
        echo json_encode(["msg"=>"Invalid input"]);
        exit;
    }

    file_put_contents($file, "$u:$p:$s:$t:$d\n", FILE_APPEND);

    echo json_encode(["msg"=>"User added"]);
break;


// ================= DELETE USER =================
case "delete":

    $u = $_GET['u'] ?? '';
    $users = readUsers($file);

    $new = "";
    foreach($users as $usr){
        if($usr['user'] != $u){
            $new .= "{$usr['user']}:{$usr['pass']}:{$usr['speed']}:{$usr['session']}:{$usr['data']}\n";
        }
    }

    file_put_contents($file, $new);

    // also remove usage
    if(file_exists($usage_file)){
        $lines = file($usage_file);
        $new = "";

        foreach($lines as $l){
            if(strpos($l, "$u:") !== 0){
                $new .= $l;
            }
        }
        file_put_contents($usage_file, $new);
    }

    // remove from active users
    if(file_exists($active_file)){
        $lines = file($active_file);
        $new = "";

        foreach($lines as $l){
            if(strpos($l, ":$u:") === false){
                $new .= $l;
            }
        }
        file_put_contents($active_file, $new);
    }

    echo json_encode(["msg"=>"User deleted"]);
break;


// ================= FLUSH SINGLE USER =================
case "flush":

    $u = $_GET['u'] ?? '';

    // reset usage
    if(file_exists($usage_file)){
        $lines = file($usage_file);
        $new = "";

        $found = false;

        foreach($lines as $l){
            $p = explode(":", trim($l));

            if($p[0] == $u){
                $new .= "$u:0:0\n";
                $found = true;
            } else {
                $new .= $l;
            }
        }

        if(!$found){
            $new .= "$u:0:0\n";
        }

        file_put_contents($usage_file, $new);
    }

    // remove from active users (force reconnect)
    if(file_exists($active_file)){
        $lines = file($active_file);
        $new = "";

        foreach($lines as $l){
            if(strpos($l, ":$u:") === false){
                $new .= $l;
            }
        }

        file_put_contents($active_file, $new);
    }

    echo json_encode(["msg"=>"User usage flushed"]);
break;


// ================= FLUSH ALL USERS =================
case "flush_all":

    // reset usage file completely
    file_put_contents($usage_file, "");

    // clear active users
    file_put_contents($active_file, "");

    echo json_encode(["msg"=>"All users usage reset"]);
break;


case "userlist":

    $users = readUsers($file);

    $usage_file = "/data/data/com.termux/files/home/router/public/user_usage.txt";
    $usage_map = [];

    if(file_exists($usage_file)){

        $lines = file($usage_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach($lines as $l){

            $l = trim($l);

            // skip invalid lines
            if(substr_count($l, ":") < 2) continue;

            $p = explode(":", $l);

            $uname = trim($p[0]);
            $data  = isset($p[1]) ? (int)$p[1] : 0;
            $time  = isset($p[2]) ? (int)$p[2] : 0;

            $usage_map[$uname] = [
                "data"=>$data,
                "time"=>$time
            ];
        }
    }

    foreach($users as &$u){

        $name = trim($u['user']);

        $u['used_data'] = isset($usage_map[$name]) ? $usage_map[$name]['data'] : 0;
        $u['used_time'] = isset($usage_map[$name]) ? $usage_map[$name]['time'] : 0;
    }

    echo json_encode($users);
break;

default:
    echo json_encode(["msg"=>"Invalid action"]);
}
