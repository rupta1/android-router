<h2 class="page-title">
    <i class="fas fa-wifi"></i> Captive Portal Management
</h2>

<style>

/* ===== TITLE ===== */
.page-title{
    font-size:22px;
    font-weight:600;
    margin-bottom:20px;
    display:flex;
    align-items:center;
    gap:10px;
}

/* ===== CARD ===== */
.card{
    background: linear-gradient(145deg,#0f172a,#1e293b);
    padding:20px;
    border-radius:16px;
    margin-bottom:20px;
    box-shadow: 0 0 25px rgba(0,0,0,0.4);
    border:1px solid rgba(255,255,255,0.05);
}

/* ===== FORM ===== */
.form{
    max-width:500px;
}

input, select{
    width:100%;
    padding:12px;
    margin-bottom:12px;
    border-radius:10px;
    border:none;
    background:#020617;
    color:white;
    outline:none;
    transition:0.3s;
}

input:focus, select:focus{
    border:1px solid #3b82f6;
    box-shadow:0 0 10px rgba(59,130,246,0.3);
}

/* ===== BUTTON ===== */
button{
    padding:10px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
    transition:0.3s;
}

.create-btn{
    width:100%;
    background:linear-gradient(90deg,#3b82f6,#2563eb);
    color:white;
}

.create-btn:hover{
    transform:scale(1.03);
}

/* ===== GRID ===== */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:18px;
}

/* ===== USER CARD ===== */
.user-card{
    background:linear-gradient(145deg,#020617,#111827);
    padding:18px;
    border-radius:14px;
    position:relative;
    overflow:hidden;
    transition:0.3s;
    border:1px solid rgba(255,255,255,0.05);
}

.user-card:hover{
    transform:translateY(-5px);
    box-shadow:0 0 20px rgba(0,0,0,0.6);
}

/* glow */
.user-card::before{
    content:"";
    position:absolute;
    inset:0;
    background:linear-gradient(120deg,transparent,#3b82f6,transparent);
    opacity:0.05;
    pointer-events:none;

}

/* ===== TITLE ===== */
.user-title{
    font-size:18px;
    font-weight:600;
    margin-bottom:10px;
    display:flex;
    align-items:center;
    gap:8px;
}

/* ===== INFO ===== */
.info{
    font-size:13px;
    margin:4px 0;
    color:#cbd5f5;
}

/* ===== BADGE ===== */
.badge{
    position:absolute;
    top:10px;
    right:10px;
    font-size:11px;
    padding:4px 8px;
    border-radius:6px;
    background:#3b82f6;
}

/* ===== RING ===== */
.ring-container{
    position:relative;
    width:120px;
    margin:12px auto;
}

.ring{
    transform:rotate(-90deg);
}

.ring-bg{
    fill:none;
    stroke:#1e293b;
    stroke-width:8;
}

.ring-fill{
    fill:none;
    stroke:#3b82f6;
    stroke-width:8;
    stroke-linecap:round;
    stroke-dasharray:326;
    stroke-dashoffset:326;
    transition:stroke-dashoffset 0.5s ease;
}

.ring-text{
    position:absolute;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    font-size:14px;
    font-weight:bold;
}

.ring,
.ring *{
    pointer-events:none;
}


/* ===== BUTTON GROUP ===== */
.btn-group{
    display:flex;
    gap:8px;
    margin-top:12px;
    position:relative;
    z-index:10;

}

.btn{
    flex:1;
    padding:8px;
    font-size:13px;
    border-radius:8px;
}

.flush{
    background:#f59e0b;
    color:white;
}

.delete{
    background:#ef4444;
    color:white;
}

.flush:hover{
    background:#d97706;
}

.delete:hover{
    background:#dc2626;
}

/* ===== TOP BUTTON ===== */
.top-btn{
    background:linear-gradient(90deg,#10b981,#059669);
    color:white;
    width:100%;
    margin-bottom:15px;
}

.top-btn:hover{
    transform:scale(1.02);
}

/* ===== ANIMATION ===== */
.user-card{
    animation:fadeIn 0.5s ease;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(10px)}
    to{opacity:1; transform:translateY(0)}
}

hr{
    border:1px solid #1e293b;
    margin:10px 0;
}

</style>

<!-- CREATE USER -->
<div class="card">
    <div class="form">
        <h3><i class="fas fa-user-plus"></i> Create User</h3>

        <input id="u" placeholder="Username">
        <input id="p" placeholder="Password">

        <label>Speed Limit</label>
        <select id="speed">
            <option value="unlimited">Unlimited</option>
            <option value="1mbit">1 Mbps</option>
            <option value="2mbit">2 Mbps</option>
            <option value="5mbit">5 Mbps</option>
            <option value="10mbit">10 Mbps</option>
        </select>

        <label>Session Time</label>
        <select id="session">
            <option value="unlimited">Unlimited</option>
            <option value="30">30 min</option>
            <option value="60">1 hour</option>
            <option value="120">2 hour</option>
        </select>

        <label>Total Data</label>
        <select id="data">
            <option value="unlimited">Unlimited</option>
            <option value="100">100 MB</option>
            <option value="500">500 MB</option>
            <option value="1024">1 GB</option>
        </select>

        <button class="create-btn" onclick="createUser()">
            <i class="fas fa-plus-circle"></i> Create User
        </button>
    </div>
</div>

<!-- USERS LIST -->
<div class="card">
    <h3><i class="fas fa-users"></i> Users</h3>

    <button class="top-btn" onclick="flushAll()">
        <i class="fas fa-broom"></i> Flush All Users
    </button>

    <div id="users" class="grid"></div>
</div>