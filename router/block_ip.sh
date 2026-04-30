#!/system/bin/sh

IP=$1
DEV="ap0"

CHAIN="USER_${IP//./_}"

ID=$(echo "$IP" | awk -F. '{print $4}')
CLASS="1:$ID"

# ================= REMOVE CLASS =================
tc class del dev "$DEV" classid "$CLASS" 2>/dev/null

# ================= REMOVE fq_codel =================
tc qdisc del dev "$DEV" parent "$CLASS" 2>/dev/null

# ================= REMOVE FILTER (FIXED) =================
tc filter show dev "$DEV" | grep "$IP" | while read line; do
    pref=$(echo "$line" | awk '{print $2}')
    tc filter del dev "$DEV" pref "$pref" 2>/dev/null
done

# ================= IPTABLES =================
iptables -D FORWARD -s "$IP" -j "$CHAIN" 2>/dev/null
iptables -D FORWARD -d "$IP" -j "$CHAIN" 2>/dev/null

iptables -D FORWARD -s "$IP" -j ACCEPT 2>/dev/null
iptables -D FORWARD -d "$IP" -j ACCEPT 2>/dev/null

iptables -t nat -D PREROUTING -s "$IP" -p tcp --dport 80 -j ACCEPT 2>/dev/null

iptables -F "$CHAIN" 2>/dev/null
iptables -X "$CHAIN" 2>/dev/null

# ===== FREEZE TIME BEFORE LOGOUT =====
TIME_FILE="/data/data/com.termux/files/home/router/public/time_$IP.txt"

if [ -f "$TIME_FILE" ]; then
    NOW=$(date +%s)
    echo "$NOW" > "$TIME_FILE"
fi

# ================= CLEAN FILES =================
sed -i "/$IP/d" /data/data/com.termux/files/home/router/active_users.txt 2>/dev/null

rm -f /data/data/com.termux/files/home/router/tmp_"$IP".txt
rm -f /data/data/com.termux/files/home/router/public/tmp_"$IP".txt
rm -f /data/data/com.termux/files/home/router/public/time_"$IP".txt

echo "User $IP removed (QoS cleaned)"