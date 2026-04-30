#!/system/bin/sh

IP=$1
SPEED=$2

DEV="ap0"
CHAIN="USER_${IP//./_}"

MAP="/data/data/com.termux/files/home/router/qos_map.txt"

touch "$MAP"

# ================= FORCE RESET =================
iptables -D FORWARD -s "$IP" -j ACCEPT 2>/dev/null
iptables -I FORWARD 1 -s "$IP" -j DROP
sleep 1
iptables -D FORWARD -s "$IP" -j DROP
iptables -I FORWARD 1 -s "$IP" -j ACCEPT

# ================= LINK SPEED =================
LINK=$(iw dev wlan0 link 2>/dev/null | grep bitrate | awk '{print $3}' | cut -d. -f1)
[ -z "$LINK" ] && LINK=100

REAL=$LINK
[ "$REAL" -lt 50 ] && REAL=50
MAX="${REAL}mbit"

# ================= INIT ROOT =================
tc qdisc show dev "$DEV" | grep -q "htb 1:"
if [ $? -ne 0 ]; then
    tc qdisc add dev "$DEV" root handle 1: htb default 999
    tc class add dev "$DEV" parent 1: classid 1:1 htb rate $MAX ceil $MAX
    tc class add dev "$DEV" parent 1:1 classid 1:999 htb rate $MAX ceil $MAX
    tc qdisc add dev "$DEV" parent 1:999 handle 999: sfq perturb 10
fi

# ================= GET OLD CLASS =================
OLD=$(grep "^$IP|" "$MAP")

if [ -n "$OLD" ]; then
    OLD_CLASS=$(echo "$OLD" | cut -d'|' -f2)
    OLD_ID=$(echo "$OLD_CLASS" | cut -d: -f2)

    # ?? REMOVE OLD FILTER BY PRIO
    tc filter del dev "$DEV" parent 1: prio "$OLD_ID" 2>/dev/null

    # REMOVE OLD CLASS
    tc class del dev "$DEV" classid "$OLD_CLASS" 2>/dev/null
    tc qdisc del dev "$DEV" parent "$OLD_CLASS" 2>/dev/null

    sed -i "/^$IP|/d" "$MAP"
fi

# ================= NEW CLASS =================
ID=$((RANDOM % 200 + 50))
CLASS="1:$ID"

if [ "$SPEED" != "unlimited" ]; then
    tc class add dev "$DEV" parent 1:1 classid "$CLASS" \
        htb rate "$SPEED" ceil "$SPEED" prio 1
else
    tc class add dev "$DEV" parent 1:1 classid "$CLASS" \
        htb rate $MAX ceil $MAX prio 2
fi

tc qdisc add dev "$DEV" parent "$CLASS" handle "${ID}0:" sfq perturb 10 2>/dev/null

# ================= APPLY FILTER =================
tc filter add dev "$DEV" protocol ip parent 1: prio "$ID" u32 \
    match ip dst "$IP" flowid "$CLASS"

tc filter add dev "$DEV" protocol ip parent 1: prio "$ID" u32 \
    match ip src "$IP" flowid "$CLASS"

# ================= SAVE MAP =================
echo "$IP|$CLASS|$SPEED" >> "$MAP"

# ================= IPTABLES =================
iptables -N "$CHAIN" 2>/dev/null
iptables -F "$CHAIN"
iptables -Z "$CHAIN" 2>/dev/null

iptables -D FORWARD -s "$IP" -j "$CHAIN" 2>/dev/null
iptables -D FORWARD -d "$IP" -j "$CHAIN" 2>/dev/null

iptables -D FORWARD -s "$IP" -j ACCEPT 2>/dev/null
iptables -D FORWARD -d "$IP" -j ACCEPT 2>/dev/null

iptables -I FORWARD 1 -s "$IP" -j "$CHAIN"
iptables -I FORWARD 2 -d "$IP" -j "$CHAIN"

iptables -I FORWARD 3 -s "$IP" -j ACCEPT
iptables -I FORWARD 4 -d "$IP" -j ACCEPT

iptables -t nat -I PREROUTING 1 -s "$IP" -p tcp --dport 80 -j ACCEPT

iptables -A "$CHAIN" -j RETURN

echo "QoS applied for $IP ($SPEED | link=${LINK}Mbps | max=$MAX)"