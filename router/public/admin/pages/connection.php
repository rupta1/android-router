<?php
function getClients() {
    $arp = file('/proc/net/arp');
    $clients = [];

    foreach ($arp as $i => $line) {
        if ($i == 0) continue;
        $p = preg_split('/\s+/', trim($line));
        if (count($p) >= 4) {
            $clients[] = ['ip'=>$p[0], 'mac'=>$p[3]];
        }
    }
    return $clients;
}

$clients = getClients();
?>

<h2 class="title"><i class="fas fa-network-wired"></i> Connected Devices</h2>

<style>

/* ===== TITLE ===== */
.title{
    margin-bottom:15px;
    font-size:18px;
}

/* ===== GRID ===== */
.device-grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(260px,1fr));
    gap:15px;
}

/* ===== CARD ===== */
.device{
    background: rgba(17,24,39,0.7);
    backdrop-filter: blur(20px);

    border-radius:14px;
    padding:15px;

    border:1px solid rgba(255,255,255,0.08);
    box-shadow:0 10px 40px rgba(0,0,0,0.6);

    transition:0.25s;
    animation:fadeIn 0.4s ease;
}

/* hover effect */
.device:hover{
    transform:translateY(-5px);
    box-shadow:0 20px 50px rgba(0,0,0,0.8);
}

/* ===== TEXT ===== */
.label{
    font-size:12px;
    color:#94a3b8;
}

.value{
    font-size:14px;
    margin-bottom:6px;
}

/* ===== STATUS ===== */
.status{
    font-weight:600;
}

.status-ok{
    color:#22c55e;
}

.status-block{
    color:#ef4444;
}

/* ===== BUTTONS ===== */
.btn-group{
    margin-top:10px;
    display:flex;
    gap:8px;
}

button{
    flex:1;
    padding:8px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:13px;
    font-weight:600;
    transition:0.2s;
}

.allow{
    background:linear-gradient(135deg,#22c55e,#16a34a);
    color:white;
}

.allow:hover{
    box-shadow:0 5px 15px rgba(34,197,94,0.4);
}

.block{
    background:linear-gradient(135deg,#ef4444,#dc2626);
    color:white;
}

.block:hover{
    box-shadow:0 5px 15px rgba(239,68,68,0.4);
}

/* ===== ANIMATION ===== */
@keyframes fadeIn{
    from{opacity:0; transform:translateY(10px)}
    to{opacity:1; transform:translateY(0)}
}

</style>


<div class="device-grid">

<?php foreach($clients as $c): 
$id = str_replace('.', '_', $c['ip']);
?>

<div class="device" data-ip="<?= $c['ip'] ?>">

<div class="label">IP Address</div>
<div class="value"><?= $c['ip'] ?></div>

<div class="label">MAC Address</div>
<div class="value"><?= $c['mac'] ?></div>

<div class="label">Status</div>
<div class="value status" id="status_<?= $id ?>">Loading...</div>

<div class="label">Speed</div>
<div class="value" id="speed_<?= $id ?>">0 KB/s</div>

<div class="label">Total Data</div>
<div class="value" id="total_<?= $id ?>">0 MB</div>

<div class="btn-group">

<form method="post" action="action.php">
<input type="hidden" name="ip" value="<?= $c['ip'] ?>">
<button name="allow" class="allow">
<i class="fas fa-check"></i> Allow
</button>
</form>

<form method="post" action="action.php">
<input type="hidden" name="ip" value="<?= $c['ip'] ?>">
<button name="block" class="block">
<i class="fas fa-ban"></i> Block
</button>
</form>

</div>

</div>
<?php endforeach; ?>

</div>