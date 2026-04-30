<h2 class="title"><i class="fas fa-cogs"></i> LAN Configuration</h2>

<style>

/* ===== GRID WRAPPER ===== */
.config-wrap{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(300px,1fr));
    gap:15px;
}

/* ===== CARD ===== */
.card{
    background: rgba(17,24,39,0.7);
    backdrop-filter: blur(20px);

    border-radius:14px;
    padding:20px;

    border:1px solid rgba(255,255,255,0.08);
    box-shadow:0 10px 40px rgba(0,0,0,0.6);

    animation:fadeIn 0.4s ease;
}

/* ===== TITLE ===== */
.title{
    margin-bottom:15px;
}

/* ===== FORM ===== */
.row{
    margin-bottom:15px;
}

label{
    display:block;
    margin-bottom:5px;
    font-size:12px;
    color:#94a3b8;
}

/* ===== INPUTS ===== */
input, select{
    width:100%;
    padding:10px;
    border-radius:8px;

    border:1px solid #334155;
    background:#020617;
    color:white;

    transition:0.2s;
}

input:focus, select:focus{
    border-color:#3b82f6;
    box-shadow:0 0 10px rgba(59,130,246,0.3);
}

/* ===== BUTTON ===== */
button{
    width:100%;
    padding:12px;

    border:none;
    border-radius:10px;

    background: linear-gradient(135deg,#3b82f6,#2563eb);
    color:white;

    font-size:15px;
    font-weight:600;

    cursor:pointer;
    transition:0.2s;
}

button:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 25px rgba(59,130,246,0.4);
}

/* ===== STATUS ===== */
.status{
    margin-top:15px;
    text-align:center;
    font-weight:600;
    font-size:14px;
}

/* ===== ICON INPUT ===== */
.input-icon{
    position:relative;
}

.input-icon i{
    position:absolute;
    left:10px;
    top:50%;
    transform:translateY(-50%);
    color:#64748b;
}

.input-icon input{
    padding-left:32px;
}

/* ===== ANIMATION ===== */
@keyframes fadeIn{
    from{opacity:0; transform:translateY(10px)}
    to{opacity:1; transform:translateY(0)}
}

</style>


<div class="config-wrap">

<div class="card">

<!-- SSID -->
<div class="row">
<label><i class="fas fa-wifi"></i> SSID</label>
<div class="input-icon">
<i class="fas fa-broadcast-tower"></i>
<input id="ssid" placeholder="Enter SSID">
</div>
</div>

<!-- PASSWORD -->
<div class="row">
<label><i class="fas fa-lock"></i> Password</label>
<div class="input-icon">
<i class="fas fa-key"></i>
<input id="password" type="password" placeholder="Min 8 characters">
</div>
</div>

<!-- SECURITY -->
<div class="row">
<label><i class="fas fa-shield-alt"></i> Security</label>
<select id="security" onchange="togglePassword()">
    <option value="wpa2">WPA2-PSK</option>
    <option value="open">Open</option>
</select>
</div>

<!-- BAND -->
<div class="row">
<label><i class="fas fa-signal"></i> Band</label>
<select id="band">
    <option value="2">2.4 GHz</option>
    <option value="5">5 GHz</option>
    <option value="any">Auto</option>
</select>
</div>

<!-- APPLY BUTTON -->
<button onclick="applyConfig()">
<i class="fas fa-check-circle"></i> Apply Settings
</button>

<!-- STATUS -->
<div class="status" id="status"></div>

</div>

</div>