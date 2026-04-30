<h2 class="title"><i class="fas fa-server"></i> System Management</h2>

<style>

/* ===== GRID ===== */
.mgmt-grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(320px,1fr));
    gap:15px;
}

/* ===== CARD ===== */
.card{
    background: rgba(17,24,39,0.75);
    backdrop-filter: blur(20px);

    border-radius:14px;
    padding:18px;

    border:1px solid rgba(255,255,255,0.08);
    box-shadow:0 10px 40px rgba(0,0,0,0.6);

    animation:fadeIn 0.4s ease;
}

/* ===== TITLE ===== */
.title{ margin-bottom:15px; }

/* ===== LABEL ===== */
.label{
    font-size:12px;
    color:#94a3b8;
}

.value{
    font-size:16px;
    font-weight:600;
}

/* ===== INPUT ===== */
input{
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #334155;
    background:#020617;
    color:white;
}

/* ===== BUTTONS ===== */
button{
    width:100%;
    padding:10px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:600;
    margin-top:6px;
    transition:0.2s;
}

.primary{ background:#3b82f6; color:white; }
.success{ background:#22c55e; color:white; }
.danger{ background:#ef4444; color:white; }

button:hover{
    transform:translateY(-2px);
}

/* ===== STATUS ===== */
.status{
    margin-top:10px;
    text-align:center;
    font-weight:600;
}

/* ===== LOG BOX ===== */
.log-box{
    background:#020617;
    padding:10px;
    border-radius:8px;
    height:200px;
    overflow:auto;
    font-size:12px;
    white-space:pre-wrap;
}

/* ===== USERS ===== */
.user{
    padding:6px;
    border-bottom:1px solid #334155;
}

/* ===== ANIMATION ===== */
@keyframes fadeIn{
    from{opacity:0; transform:translateY(10px)}
    to{opacity:1; transform:translateY(0)}
}

</style>

<div class="mgmt-grid">

<!-- ===== SYSTEM STATUS ===== -->
<div class="card">
<h3><i class="fas fa-chart-line"></i> System Status</h3>

<div class="label">CPU Usage</div>
<div class="value" id="cpu">Loading...</div>

<div class="label">RAM Usage</div>
<div class="value" id="ram">Loading...</div>

<div class="label">Uptime</div>
<div class="value" id="uptime">Loading...</div>
</div>


<!-- ===== ADMIN SETTINGS ===== -->
<div class="card">
<h3><i class="fas fa-user-shield"></i> Admin Account</h3>

<input id="user" placeholder="New Username">
<input id="pass" type="password" placeholder="New Password">

<button class="primary" onclick="changeAdmin()">Update</button>
<div id="adminStatus" class="status"></div>
</div>


<!-- ===== ROUTER CONTROL ===== -->
<div class="card">
<h3><i class="fas fa-power-off"></i> Router Control</h3>

<button class="success" onclick="restartRouter()">Restart</button>
<button class="danger" onclick="stopRouter()">Stop</button>

<div id="routerStatus" class="status"></div>
</div>


<!-- ===== BACKUP / RESTORE ===== -->
<div class="card">
<h3><i class="fas fa-database"></i> Backup / Restore</h3>

<button class="primary" onclick="backupConfig()">Download Backup</button>
<button class="danger" onclick="restoreConfig()">Restore Backup</button>

<div id="backupStatus" class="status"></div>
</div>


<!-- ===== ACTIVE USERS ===== -->
<div class="card">
<h3><i class="fas fa-users"></i> Active Users</h3>

<div id="usersList">Loading...</div>
</div>


<!-- ===== LOG VIEWER ===== -->
<div class="card" style="grid-column:span 2;">
<h3><i class="fas fa-file-alt"></i> System Logs</h3>

<div class="log-box" id="logs">Loading logs...</div>

<button onclick="clearLogs()" class="danger">Clear Logs</button>
</div>

</div>