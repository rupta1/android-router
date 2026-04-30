function initConnectionPage(){
let lastBytes = {};

function updateDevice(dev){
    let ip = dev.dataset.ip;
    let id = ip.replace(/\./g,'_');

    let statusEl = document.getElementById("status_"+id);
    let speedEl  = document.getElementById("speed_"+id);
    let totalEl  = document.getElementById("total_"+id);

    if(!statusEl || !speedEl || !totalEl) return;

    // STATUS
    fetch("../../check_status.php?ip="+ip)
    .then(r=>r.text())
    .then(status=>{
        if(status.trim() === "allowed"){
            statusEl.innerText = "Allowed";
            statusEl.className = "status status-ok";
        } else {
            statusEl.innerText = "Blocked";
            statusEl.className = "status status-block";
        }
    })
    .catch(()=>statusEl.innerText="Error");

    // USAGE
    fetch("/usage.php?ip="+ip)
    .then(r=>r.json())
    .then(data=>{
        let bytes = data.bytes || 0;

        if(!lastBytes[ip]) lastBytes[ip] = bytes;

        let speed = (bytes - lastBytes[ip]) / 1024 / 3;
        lastBytes[ip] = bytes;

        speedEl.innerText = speed.toFixed(2) + " KB/s";
        totalEl.innerText = (bytes/1024/1024).toFixed(2) + " MB";
    })
    .catch(()=>{});
}

function refreshAll(){
    document.querySelectorAll('.device').forEach(updateDevice);
}

// run immediately + interval
setTimeout(refreshAll, 500);
setInterval(refreshAll, 3000);
}
