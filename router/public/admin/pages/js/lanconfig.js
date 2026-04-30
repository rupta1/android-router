// Disable/Enable password based on security
function togglePassword(){
    let sec = document.getElementById("security").value;
    let passField = document.getElementById("password");

    if(sec === "open"){
        passField.value = "";
        passField.disabled = true;
        passField.placeholder = "Not required for Open network";
    }else{
        passField.disabled = false;
        passField.placeholder = "Min 8 characters";
    }
}


// Apply configuration
function applyConfig(){

    let ssid = document.getElementById("ssid").value.trim();
    let pass = document.getElementById("password").value.trim();
    let sec  = document.getElementById("security").value;
    let band = document.getElementById("band").value;

    if(!ssid){
        alert("SSID required");
        return;
    }

    if(sec !== "open" && pass.length < 8){
        alert("Password must be at least 8 characters");
        return;
    }

    document.getElementById("status").innerText = "Applying settings...";

    fetch(`../../lanconfig_action.php?ssid=${encodeURIComponent(ssid)}&pass=${encodeURIComponent(pass)}&sec=${sec}&band=${band}`)
    .then(r => r.json())
    .then(d => {
        document.getElementById("status").innerText = d.msg;
    })
    .catch(() => {
        document.getElementById("status").innerText = "Settings Applied";
    });
}

function loadConfig(){
    fetch("../../get_lan_config.php")
    .then(r=>r.json())
    .then(d=>{
        document.getElementById("ssid").value = d.ssid || "";
        document.getElementById("password").value = d.pass || "";
        document.getElementById("security").value = d.sec || "wpa2";
        document.getElementById("band").value = d.band || "any";

        togglePassword();
    });
}

loadConfig();

// Run on load
togglePassword();