<h2 class="title"><i class="fas fa-shield-alt"></i> Firewall Panel</h2>

<style>

/* ===== GRID ===== */
.firewall-grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(300px,1fr));
    gap:15px;
}

/* ===== CARD ===== */
.card{
    background: rgba(17,24,39,0.7);
    backdrop-filter: blur(20px);

    border-radius:14px;
    padding:18px;

    border:1px solid rgba(255,255,255,0.08);
    box-shadow:0 10px 40px rgba(0,0,0,0.6);

    animation:fadeIn 0.4s ease;
}

/* ===== TITLE ===== */
.title{
    margin-bottom:15px;
}

/* ===== LABEL ===== */
h3{
    margin-bottom:10px;
}

/* ===== INPUT ===== */
.input-row{
    display:flex;
    gap:8px;
    margin-bottom:10px;
}

input{
    flex:1;
    padding:10px;

    border-radius:8px;
    border:1px solid #334155;

    background:#020617;
    color:white;
}

input:focus{
    border-color:#3b82f6;
}

/* ===== BUTTON ===== */
button{
    padding:10px;

    border:none;
    border-radius:8px;
    cursor:pointer;

    font-weight:600;
    transition:0.2s;
}

button:hover{
    transform:translateY(-2px);
}

/* action buttons */
.primary{
    background:linear-gradient(135deg,#3b82f6,#2563eb);
    color:white;
}

.danger{
    background:linear-gradient(135deg,#ef4444,#dc2626);
    color:white;
}

.success{
    background:linear-gradient(135deg,#22c55e,#16a34a);
    color:white;
}

/* ===== LIST ===== */
ul{
    list-style:none;
    padding:0;
}

li{
    background:#020617;
    margin:6px 0;
    padding:8px;

    border-radius:8px;

    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* ===== REMOVE BTN ===== */
.del{
    background:#ef4444;
    color:white;
    padding:4px 8px;
    border-radius:6px;
}

/* ===== PROXY STATUS ===== */
.status{
    margin-top:10px;
    text-align:center;
    font-weight:600;
}

.status-on{
    color:#22c55e;
}

.status-off{
    color:#ef4444;
}

/* ===== ANIMATION ===== */
@keyframes fadeIn{
    from{opacity:0; transform:translateY(10px)}
    to{opacity:1; transform:translateY(0)}
}

</style>


<div class="firewall-grid">

<!-- ===== MAC FILTER ===== -->
<div class="card">
<h3><i class="fas fa-network-wired"></i> MAC Filter</h3>

<div class="input-row">
<input id="mac" placeholder="AA:BB:CC:DD:EE:FF">
<button class="primary" onclick="addMac()">
<i class="fas fa-ban"></i>
</button>
</div>

<ul id="macList"></ul>
</div>


<!-- ===== URL BLOCK ===== -->
<div class="card">
<h3><i class="fas fa-globe"></i> URL Block</h3>

<div class="input-row">
<input id="url" placeholder="example.com">
<button class="primary" onclick="addURL()">
<i class="fas fa-ban"></i>
</button>
</div>

<ul id="urlList"></ul>
</div>


<!-- ===== PROXY ===== -->
<div class="card">
<h3><i class="fas fa-server"></i> Proxy</h3>

<div class="input-row">
<input id="proxy_ip" placeholder="Proxy IP">
</div>

<div class="input-row">
<input id="proxy_port" placeholder="Port">
</div>

<div class="input-row">
<button class="success" onclick="enableProxy()">
<i class="fas fa-play"></i> Enable
</button>

<button class="danger" onclick="disableProxy()">
<i class="fas fa-stop"></i> Disable
</button>
</div>

<div id="proxyStatus" class="status"></div>
</div>

</div>