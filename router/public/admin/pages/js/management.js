function changeAdmin(){

    let user = document.getElementById("user").value.trim();
    let pass = document.getElementById("pass").value.trim();

    if(!user || !pass){
        alert("All fields required");
        return;
    }

    if(pass.length < 6){
        alert("Password must be at least 6 characters");
        return;
    }

    document.getElementById("adminStatus").innerText = "Updating...";

    fetch(`../../management_action.php?action=changeAdmin&user=${encodeURIComponent(user)}&pass=${encodeURIComponent(pass)}`)
    .then(r=>r.json())
    .then(d=>{
        document.getElementById("adminStatus").innerText = d.msg;
    });
}


function restartRouter(){
    document.getElementById("routerStatus").innerText = "Restarting...";

    fetch("../../management_action.php?action=restart")
    .then(r=>r.json())
    .then(d=>{
        document.getElementById("routerStatus").innerText = d.msg;
    });
}


// ===== SYSTEM MONITOR =====
function loadSystem(){

    fetch("../../system_info.php")
    .then(r=>r.json())
    .then(d=>{
        document.getElementById("cpu").innerText = d.cpu + "%";
        document.getElementById("ram").innerText = d.ram + "%";
        document.getElementById("uptime").innerText = d.uptime;
    });
}

// ===== USERS =====
function loadUsers(){

    fetch("/get_active_users.php")
    .then(res => res.json())
    .then(data => {

        let box = document.getElementById("usersList");
        box.innerHTML = "";

        if(!data.length){
            box.innerHTML = "No active users";
            return;
        }

        data.forEach(u => {
            box.innerHTML += `
                <div class="user">
                    <b>${u.user}</b><br>
                    IP: ${u.ip}<br>
                    Limit: ${u.limit}
                </div>
            `;
        });

    })
    .catch(err => {
        console.error("Users JSON error:", err);
    });
}
// ===== LOGS =====
function loadLogs(){

    fetch("../../logs.txt")
    .then(r=>r.text())
    .then(t=>{
        document.getElementById("logs").innerText = t;
    });
}

function clearLogs(){
    fetch("../../management_action.php?action=clear_logs")
    .then(()=> loadLogs());
}

// ===== BACKUP =====
function backupConfig(){

    fetch("../../management_action.php?action=backup")
    .then(r => r.json())
    .then(d => {

        if(d.url){
            window.location = d.url;
        } else {
            alert("Backup failed");
        }

    });
}

// ===== RESTORE =====
function restoreConfig(){
    alert("Place backup file manually (server-side)");
}

function stopRouter(){
    document.getElementById("routerStatus").innerText = "Stopping...";

    fetch("../../management_action.php?action=stop")
    .then(r=>r.json())
    .then(d=>{
        document.getElementById("routerStatus").innerText = d.msg;
    });
}

// ===== INIT =====
setInterval(()=>{
    loadSystem();
    loadUsers();
    loadLogs();
},3000);


loadSystem();
loadUsers();
loadLogs();
