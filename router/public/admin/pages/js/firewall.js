function alertBox(msg){
    alert(msg);
}

// -------- MAC --------
function addMac(){
    let mac = document.getElementById("mac").value;

    fetch(`../../firewall_action.php?action=block_mac&mac=${mac}`)
    .then(()=>{ alertBox("MAC Blocked"); loadRules(); });
}

function removeMac(mac){
    fetch(`../../firewall_action.php?action=remove_mac&mac=${mac}`)
    .then(()=>{ alertBox("MAC Removed"); loadRules(); });
}

// -------- URL --------
function addURL(){

    let url = document.getElementById("url").value;
    let ip  = prompt("Enter device IP:");

    if(!ip) return;

    fetch(`../../firewall_action.php?action=block_url&ip=${ip}&url=${url}`)
    .then(()=>{ alertBox("Blocked for "+ip); loadRules(); });
}

function removeURL(ip,url){

    fetch(`../../firewall_action.php?action=remove_url&ip=${ip}&url=${url}`)
    .then(()=>{ alertBox("Removed"); loadRules(); });
}

// -------- PROXY --------
function enableProxy(){
    let ip = document.getElementById("proxy_ip").value;
    let port = document.getElementById("proxy_port").value;

    fetch(`../../firewall_action.php?action=proxy_on&ip=${ip}&port=${port}`)
    .then(()=>{ alertBox("Proxy Enabled"); loadRules(); });
}

function disableProxy(){
    fetch(`../../firewall_action.php?action=proxy_off`)
    .then(()=>{ alertBox("Proxy Disabled"); loadRules(); });
}

// -------- LOAD --------
function loadRules(){

    fetch("../../firewall_action.php?action=list")
    .then(r=>r.json())
    .then(d=>{

        // MAC
        let macList = document.getElementById("macList");
        macList.innerHTML = "";

        d.mac.forEach(m=>{
            macList.innerHTML += `<li>${m} <button class="del" onclick="removeMac('${m}')">X</button></li>`;
        });

        // URL
        let urlList = document.getElementById("urlList");
        urlList.innerHTML = "";

        d.url.forEach(u=>{
            urlList.innerHTML += `
                <li>
                    ${u.url} (IP: ${u.ip})
                    <button class="del" onclick="removeURL('${u.ip}','${u.url}')">X</button>
                </li>`;
        });

        // Proxy
        document.getElementById("proxyStatus").innerText =
            d.proxy ? "Enabled: "+d.proxy : "Disabled";
    });
}

setInterval(loadRules,2000);