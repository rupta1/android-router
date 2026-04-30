<?php
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

$file = "/data/data/com.termux/files/home/router/public/admin_db.txt";

switch($action){

// ================= ADMIN CHANGE =================
case "changeAdmin":

    $user = $_GET['user'] ?? '';
    $pass = $_GET['pass'] ?? '';

    if(!$user || !$pass){
        echo json_encode(["msg"=>"Invalid input"]);
        exit;
    }

    file_put_contents($file, "$user:$pass");

    echo json_encode(["msg"=>"Admin credentials updated"]);
    break;


// ================= ROUTER CONTROL =================
case "restart":

    shell_exec("
    nohup sh -c '
        LOG=/data/data/com.termux/files/home/router/public/logs.txt
        LOCK=/data/data/com.termux/files/home/router/restart.lock

        # ===== PREVENT MULTIPLE RUN =====
        if [ -f \$LOCK ]; then
            echo \"[\$(date)] Restart already running\" >> \$LOG
            exit 0
        fi

        touch \$LOCK

        echo \"[\$(date)] Restart initiated\" >> \$LOG

        # ===== STEP 1: STOP (ROOT CONTEXT) =====
        su -c /data/data/com.termux/files/home/stop-router.sh >> \$LOG 2>&1

        echo \"[\$(date)] Stop completed\" >> \$LOG

        # ===== STEP 2: COOL DOWN =====
        echo \"[\$(date)] Cooling down (10s)...\" >> \$LOG
        sleep 10

        # ===== STEP 3: START (ROOT CONTEXT + DETACHED) =====
        su -c \"nohup sh /data/data/com.termux/files/home/start-router.sh >> /data/data/com.termux/files/home/router/public/logs.txt 2>&1 &\"

        echo \"[\$(date)] Start triggered via su -c\" >> \$LOG

        rm -f \$LOCK
    ' >/dev/null 2>&1 &
    ");

    echo json_encode(["msg"=>"Router restarting..."]);
    break;

case "stop":

    shell_exec("nohup /data/data/com.termux/files/home/stop-router.sh >/dev/null 2>&1 &");

    echo json_encode(["msg"=>"Router stopping..."]);
    break;


// ================= LOG CLEAR =================
case "clear_logs":

    file_put_contents("/data/data/com.termux/files/home/router/public/logs.txt", "");

    echo json_encode(["msg"=>"Logs cleared"]);
    break;


// ================= BACKUP =================
case "backup":

    $dir = "/data/data/com.termux/files/home/router/public/backup";

    // create folder if not exists
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $filename = "backup_" . time() . ".zip";
    $fullpath = $dir . "/" . $filename;

    // create zip
    shell_exec("zip -r $fullpath /data/data/com.termux/files/home/router/ >/dev/null 2>&1");

    // ?? IMPORTANT: return WEB URL not file path
    $url = "/backup/" . $filename;

    echo json_encode([
        "status" => "ok",
        "url" => $url
    ]);

    break;

default:
    echo json_encode(["msg"=>"Invalid action"]);
}