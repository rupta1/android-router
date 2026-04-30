<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Router Panel</title>

<link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',system-ui;
}

body{
    display:flex;
    height:100vh;
    background: radial-gradient(circle at top,#1e293b,#020617);
    color:white;
}

/* ===== SIDEBAR ===== */
.sidebar{
    width:240px;
    background: rgba(2,6,23,0.85);
    backdrop-filter: blur(20px);
    border-right:1px solid rgba(255,255,255,0.08);
    transition:0.3s;
    display:flex;
    flex-direction:column;
}

/* collapsed */
.sidebar.collapsed{
    width:70px;
}

/* logo */
.sidebar h2{
    text-align:center;
    padding:20px;
    font-size:18px;
    border-bottom:1px solid rgba(255,255,255,0.05);
    transition:0.3s;
}

/* hide text when collapsed */
.sidebar.collapsed h2 span{
    display:none;
}

/* menu */
.sidebar a{
    padding:14px 18px;
    color:#cbd5f5;
    text-decoration:none;
    display:flex;
    align-items:center;
    gap:12px;

    transition:0.25s;
    border-left:3px solid transparent;
}

/* icon */
.sidebar a i{
    width:20px;
    text-align:center;
    font-size:16px;
}

/* collapse text */
.sidebar.collapsed a span{
    display:none;
}

/* hover */
.sidebar a:hover{
    background:rgba(30,41,59,0.6);
    color:white;
    border-left:3px solid #3b82f6;
}

/* active */
.sidebar a.active{
    background:rgba(30,41,59,0.8);
    border-left:3px solid #3b82f6;
    color:white;
}

/* logout */
.sidebar a.logout{
    margin-top:auto;
    color:#ef4444;
}

/* ===== CONTENT ===== */
.content{
    flex:1;
    display:flex;
    flex-direction:column;
}

/* ===== TOPBAR ===== */
.topbar{
    height:60px;
    display:flex;
    align-items:center;
    gap:15px;
    padding:0 20px;

    background: rgba(17,24,39,0.7);
    backdrop-filter: blur(20px);

    border-bottom:1px solid rgba(255,255,255,0.08);
}

/* toggle btn */
.toggle-btn{
    font-size:18px;
    cursor:pointer;
    color:#cbd5f5;
}

/* content body */
.main{
    padding:20px;
    overflow:auto;
}

/* card */
.card{
    background: rgba(17,24,39,0.7);
    backdrop-filter: blur(20px);

    border-radius:14px;
    padding:18px;

    border:1px solid rgba(255,255,255,0.08);
    box-shadow:0 10px 40px rgba(0,0,0,0.6);

    animation:fadeIn 0.4s ease;
}

/* animation */
@keyframes fadeIn{
    from{opacity:0;transform:translateY(10px)}
    to{opacity:1;transform:translateY(0)}
}
</style>

<body>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar" id="sidebar">

<h2><i class="fas fa-broadcast-tower"></i> <span>Router</span></h2>

<a onclick="loadPage('connection',this)" class="active">
<i class="fas fa-network-wired"></i> <span>Connection</span>
</a>

<a onclick="loadPage('wan',this)">
<i class="fas fa-globe"></i> <span>WAN</span>
</a>

<a onclick="loadPage('lan',this)">
<i class="fas fa-laptop"></i> <span>LAN</span>
</a>

<a onclick="loadPage('lanconfig',this)">
<i class="fas fa-cogs"></i> <span>Configure LAN</span>
</a>

<a onclick="loadPage('firewall',this)">
<i class="fas fa-shield-alt"></i> <span>Firewall</span>
</a>

<a onclick="loadPage('management',this)">
<i class="fas fa-tools"></i> <span>Management</span>
</a>

<a onclick="loadPage('captive',this)">
<i class="fas fa-wifi"></i> <span>Captive Portal</span>
</a>

<a href="logout.php" class="logout">
<i class="fas fa-sign-out-alt"></i> <span>Logout</span>
</a>

</div>


<!-- ===== CONTENT ===== -->
<div class="content">

<!-- TOPBAR -->
<div class="topbar">
<i class="fas fa-bars toggle-btn" onclick="toggleSidebar()"></i>
<span><i class="fas fa-gauge"></i> Dashboard</span>
</div>

<div class="main">
<div id="content" class="card">Loading...</div>
</div>

</div>


<script>

// ===== SIDEBAR TOGGLE =====
function toggleSidebar(){
    document.getElementById("sidebar").classList.toggle("collapsed")
}


// ===== PAGE LOAD =====
function loadPage(page, el=null){

    document.querySelectorAll(".sidebar a").forEach(a=>{
        a.classList.remove("active");
    });

    if(el) el.classList.add("active");

    fetch("pages/" + page + ".php")
    .then(res => res.text())
    .then(html => {

        const content = document.getElementById("content");
        content.innerHTML = html;

        const oldScript = document.getElementById("page-script");
        if (oldScript) oldScript.remove();

        const script = document.createElement("script");
        script.src = "pages/js/" + page + ".js";
        script.id = "page-script";

        script.onload = () => {
            const fn = window["init" + page.charAt(0).toUpperCase() + page.slice(1) + "Page"];
            if (typeof fn === "function") fn();
        };

        document.body.appendChild(script);
    });
}

// default load
loadPage("connection", document.querySelector(".sidebar a"));

</script>

<script src="/assets/chartjs/chart.js"></script>


</body>
</html>
