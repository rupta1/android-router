#!/system/bin/sh

# ---- Termux PATH ----
export PREFIX=/data/data/com.termux/files/usr
export PATH=$PREFIX/bin:$PATH

LOG="/data/data/com.termux/files/home/router/public/logs.txt"

log(){
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG"
}

log "===== START ROUTER INIT ====="

# ---- Load saved LAN configuration ----
log "Loading LAN configuration..."
/data/data/com.termux/files/home/router/load_lan_config.sh >> "$LOG" 2>&1
log "LAN configuration applied"

# ---- Detect hotspot IP ----
IP=$(ip -4 addr show ap0 | grep inet | awk '{print $2}' | cut -d/ -f1)
if [ -z "$IP" ]; then
    log "ERROR: Hotspot OFF"
    echo "Hotspot OFF. Turn it ON first."
    exit 1
fi

echo "Hotspot IP: $IP"
log "Hotspot IP detected: $IP"

# ---- Enable routing ----
echo 1 > /proc/sys/net/ipv4/ip_forward
log "IP forwarding enabled"

# ---- Clean rules ----
iptables -t nat -F
iptables -F
log "iptables cleaned"

# ---- Internet NAT ----
iptables -t nat -A POSTROUTING -o wlan0 -j MASQUERADE
iptables -A FORWARD -i ap0 -o wlan0 -j ACCEPT
iptables -A FORWARD -i wlan0 -o ap0 -m state --state RELATED,ESTABLISHED -j ACCEPT
log "NAT and forwarding rules applied"

# ---- Captive portal: block all clients initially ----
iptables -I FORWARD -i ap0 -j DROP
log "Captive portal default block applied"

# ---- Redirect HTTP to portal ----
iptables -t nat -A PREROUTING -i ap0 -p tcp --dport 80 -j DNAT --to $IP:80
log "HTTP redirected to captive portal"

# ---- Start PHP server (from router/public) ----
pkill php 2>/dev/null
log "Old PHP process killed"

# ?? FORCE KILL ANY LEFTOVER
pkill -9 php 2>/dev/null
sleep 2

cd /data/data/com.termux/files/home/router/public || {
    log "ERROR: public directory not found"
    exit 1
}

# ?? EXTRA DELAY (CRITICAL FIX)
sleep 2

php -S 0.0.0.0:80 >> "$LOG" 2>&1 &
log "PHP server started"

echo "Router + Captive Portal running at http://$IP"
log "Router started successfully at http://$IP"

# ---- Start usage monitor (FIXED) ----
pkill -f monitor_usage.sh 2>/dev/null
log "Old monitor process killed"

su -c "sh /data/data/com.termux/files/home/router/monitor_usage.sh >> /data/data/com.termux/files/home/router/public/logs.txt 2>&1 &"
log "Monitor started"

log "===== START ROUTER END ====="