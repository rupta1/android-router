function initWanPage(){

    let chart, rxArr=[], txArr=[], labels=[];

    function initChart(){
        const ctx = document.getElementById('wanChart');

        chart = new Chart(ctx,{
            type:'line',
            data:{
                labels:labels,
                datasets:[
                    {
                        label:'Download',
                        data:rxArr,
                        borderColor:'#3b82f6',
                        tension:0.3
                    },
                    {
                        label:'Upload',
                        data:txArr,
                        borderColor:'#ef4444',
                        tension:0.3
                    }
                ]
            },
            options:{
                responsive:true,
                animation:false,
                plugins:{
                    legend:{labels:{color:'white'}}
                },
                scales:{
                    x:{ticks:{color:'#94a3b8'}},
                    y:{ticks:{color:'#94a3b8'}}
                }
            }
        });
    }

    function update(){

        fetch("../../wan_info.php")
        .then(r=>r.json())
        .then(d=>{

            let iface = d.iface || "Unknown";

            document.getElementById("iface").innerText = iface;
            document.getElementById("ip").innerText = d.ip || "N/A";
            document.getElementById("ipv6").innerText = d.ipv6 || "N/A";

            let st = document.getElementById("status");

            if(d.ip){
                st.innerText = "Connected";
                st.className = "value status-ok";
            } else {
                st.innerText = "Disconnected";
                st.className = "value status-bad";
            }

            fetch("../../wan_stats.php?iface="+iface)
            .then(r=>r.json())
            .then(s=>{

                let rx = s.rx || 0;
                let tx = s.tx || 0;

                let spRX = (rx - (window.lastRX||0)) / 1024 / 2;
                let spTX = (tx - (window.lastTX||0)) / 1024 / 2;

                window.lastRX = rx;
                window.lastTX = tx;

                document.getElementById("rx").innerText = spRX.toFixed(2)+" KB/s";
                document.getElementById("tx").innerText = spTX.toFixed(2)+" KB/s";

                document.getElementById("trx").innerText = (rx/1024/1024).toFixed(2)+" MB";
                document.getElementById("ttx").innerText = (tx/1024/1024).toFixed(2)+" MB";

                // ===== GRAPH =====
                let time = new Date().toLocaleTimeString();

                labels.push(time);
                rxArr.push(spRX);
                txArr.push(spTX);

                if(labels.length > 20){
                    labels.shift();
                    rxArr.shift();
                    txArr.shift();
                }

                chart.update();
            });
        });
    }

    initChart();
    update();
    setInterval(update,2000);
}