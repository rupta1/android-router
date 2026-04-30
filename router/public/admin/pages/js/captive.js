var usersMap = {}; // store existing users

// ================= FORMAT TIME =================
function formatTime(sec){
    sec = parseInt(sec) || 0;

    let h = Math.floor(sec/3600);
    let m = Math.floor((sec%3600)/60);
    let s = sec%60;

    return `${h}h ${m}m ${s}s`;
}

function formatSpeed(speed){

    if(!speed || speed === "unlimited") return "Unlimited";

    let s = speed.toLowerCase();

    if(s.includes("mbit")){
        return s.replace("mbit"," Mbps");
    }

    if(s.includes("kbit")){
        return s.replace("kbit"," Kbps");
    }

    if(s.includes("gbit")){
        return s.replace("gbit"," Gbps");
    }

    return speed; // fallback
}

// ================= CREATE USER CARD =================
function createUserCard(u){

    let container = document.getElementById("users");

    let card = document.createElement("div");
    card.className = "user-card";
    card.id = "card_" + u.user;

    card.innerHTML = `
        <div class="badge">Quota: ${u.data} MB</div>

        <div class="user-title">
            <i class="fas fa-user"></i> ${u.user}
        </div>

        <div class="info">Speed: ${formatSpeed(u.speed)}</div>
        <div class="info">Session: ${u.session} Minutes</div>

        <hr>

        <div class="ring-container">
            <svg class="ring" width="120" height="120">
                <circle cx="60" cy="60" r="52" class="ring-bg"/>
                <circle cx="60" cy="60" r="52" class="ring-fill"
                    id="ring_${u.user}"/>
            </svg>

            <div class="ring-text" id="ringText_${u.user}">
                0%
            </div>
        </div>

        <div class="info" id="data_${u.user}">Used Data: 0 MB</div>
        <div class="info" id="time_${u.user}">Used Time: 0s</div>

        <div class="btn-group">
            <button class="btn flush" onclick="flushUser('${u.user}')">Flush</button>
            <button class="btn delete" onclick="deleteUser('${u.user}')">Delete</button>
        </div>
    `;

    container.appendChild(card);
    usersMap[u.user] = true;
}

// ================= UPDATE USER =================
function updateUser(u){

    let dataBytes = parseInt(u.used_data) || 0;
    let timeSec   = parseInt(u.used_time) || 0;

    let usedMB = (dataBytes / 1024 / 1024).toFixed(2);

    // update text
    let dataEl = document.getElementById("data_"+u.user);
    let timeEl = document.getElementById("time_"+u.user);

    if(dataEl) dataEl.innerText = `Used Data: ${usedMB} MB`;
    if(timeEl) timeEl.innerText = `Used Time: ${formatTime(timeSec)}`;

    // ===== PROGRESS RING =====
    let ring = document.getElementById("ring_"+u.user);
    let text = document.getElementById("ringText_"+u.user);

    if(ring){

        let percent = 0;

        if(u.data !== "unlimited"){
            let limitBytes = parseInt(u.data) * 1024 * 1024;
            percent = Math.min((dataBytes / limitBytes) * 100, 100);
        }

        let circumference = 326;
        let offset = circumference - (circumference * percent / 100);

        ring.style.strokeDasharray = circumference;
        ring.style.strokeDashoffset = offset;

        if(text) text.innerText = Math.floor(percent) + "%";
    }
}

// ================= REMOVE OLD USERS =================
function removeOldUsers(data){

    let currentUsers = data.map(u => u.user);

    Object.keys(usersMap).forEach(user => {
        if(!currentUsers.includes(user)){
            let card = document.getElementById("card_"+user);
            if(card) card.remove();
            delete usersMap[user];
        }
    });
}

// ================= MAIN LOAD =================
function loadUsers(){

    fetch("../../users_action.php?action=userlist&nocache=" + Date.now())
    .then(r => r.json())
    .then(data => {

        data.forEach(u => {

            // create if not exists
            if(!usersMap[u.user]){
                createUserCard(u);
            }

            // update always
            updateUser(u);
        });

        removeOldUsers(data);

    })
    .catch(() => console.log("Error loading users"));
}

// ================= AUTO REFRESH =================
setInterval(loadUsers, 2000);


// ================= CREATE =================
function createUser(){

    let u = document.getElementById("u").value;
    let p = document.getElementById("p").value;
    let s = document.getElementById("speed").value;
    let t = document.getElementById("session").value;
    let d = document.getElementById("data").value;

    if(!u || !p){
        alert("Username & password required");
        return;
    }

    fetch(`../../users_action.php?action=create&u=${u}&p=${p}&s=${s}&t=${t}&d=${d}`)
    .then(() => loadUsers());
}

// ================= DELETE =================
function deleteUser(u){

    if(!confirm("Delete user " + u + "?")) return;

    fetch(`../../users_action.php?action=delete&u=${u}`)
    .then(() => loadUsers());
}

// ================= FLUSH =================
function flushUser(u){

    if(!confirm("Reset usage for " + u + "?")) return;

    fetch(`../../users_action.php?action=flush&u=${u}`)
    .then(() => loadUsers());
}

// ================= FLUSH ALL =================
function flushAll(){

    if(!confirm("Reset ALL users usage?")) return;

    fetch(`../../users_action.php?action=flush_all`)
    .then(() => loadUsers());
}

// ================= INIT =================
loadUsers();