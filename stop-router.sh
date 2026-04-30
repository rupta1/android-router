#!/system/bin/sh

export PREFIX=/data/data/com.termux/files/usr
export PATH=$PREFIX/bin:$PATH

LOG="/data/data/com.termux/files/home/router/public/logs.txt"

log(){
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG"
}

log "===== STOP ROUTER INIT ====="

echo "Stopping router..."
log "Stopping router script started"

# ================= STOP PHP =================
pkill php 2>/dev/null
log "PHP server stopped"

# ================= STOP MONITOR =================
pkill -f monitor_usage.sh 2>/dev/null
log "Monitor script stopped"

# ================= FLUSH IPTABLES =================
iptables -F
iptables -t nat -F
log "iptables flushed"

# ================= DISABLE FORWARDING =================
echo 0 > /proc/sys/net/ipv4/ip_forward
log "IP forwarding disabled"

# ================= RESTORE ANDROID HOTSPOT =================
log "Stopping custom hotspot..."
su -c "cmd wifi stop-softap"
sleep 2

log "Resetting band config..."
su -c "cmd wifi force-softap-band disabled"

# ================= RESTORE NAT (YOUR ORIGINAL LOGIC) =================
iptables -t nat -A POSTROUTING -o wlan0 -j MASQUERADE
iptables -A FORWARD -i ap0 -o wlan0 -j ACCEPT
iptables -A FORWARD -i wlan0 -o ap0 -m state --state RELATED,ESTABLISHED -j ACCEPT

log "Default NAT rules applied"

# ================= CLEAN ACTIVE USERS =================
ACTIVE="/data/data/com.termux/files/home/router/active_users.txt"

if [ -f "$ACTIVE" ]; then
    > "$ACTIVE"
    log "active_users.txt cleared"
else
    log "active_users.txt not found"
fi

# ================= CLEAN TEMP FILES =================
PUBLIC="/data/data/com.termux/files/home/router/public"

rm -f "$PUBLIC"/tmp_*.txt
rm -f "$PUBLIC"/time_*.txt

log "Temporary usage files cleared"

# ================= OPTIONAL EXTRA CLEAN =================
rm -f "$PUBLIC"/user_usage.txt 2>/dev/null
log "user_usage.txt cleared (optional reset)"

echo "Hotspot internet restored (no reboot needed)"
log "Router stopped successfully"

log "===== STOP ROUTER END ====="