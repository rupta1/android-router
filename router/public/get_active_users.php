<?php
header('Content-Type: application/json');

$file = "/data/data/com.termux/files/home/router/active_users.txt";

$users = [];

if(file_exists($file)){
    foreach(file($file) as $line){

        $p = explode(":", trim($line));

        if(count($p) >= 3){
            $users[] = [
                "ip" => $p[0],
                "user" => $p[1],
                "limit" => $p[2]
            ];
        }
    }
}

echo json_encode($users);