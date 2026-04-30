#!/system/bin/sh

CONFIG="/data/data/com.termux/files/home/router/lan_config.txt"
LOG="/data/data/com.termux/files/home/router/public/logs.txt"

echo "========== $(date) ==========" >> "$LOG"
echo "[LAN] Loading configuration..." >> "$LOG"

# ================= CHECK CONFIG =================
if [ ! -f "$CONFIG" ]; then
    echo "[LAN] No config file found. Skipping." >> "$LOG"
    exit 0
fi

# ================= READ CONFIG =================
SSID=$(grep '^SSID=' "$CONFIG" | cut -d= -f2)
PASS=$(grep '^PASS=' "$CONFIG" | cut -d= -f2)
SEC=$(grep '^SEC=' "$CONFIG" | cut -d= -f2)
BAND=$(grep '^BAND=' "$CONFIG" | cut -d= -f2)

# fallback defaults
[ -z "$SSID" ] && SSID="MyHotspot"
[ -z "$SEC" ] && SEC="wpa2"
[ -z "$BAND" ] && BAND="any"

echo "[LAN] SSID=$SSID" >> "$LOG"
echo "[LAN] SECURITY=$SEC" >> "$LOG"
echo "[LAN] BAND=$BAND" >> "$LOG"

# ================= STOP HOTSPOT =================
echo "[LAN] Stopping hotspot..." >> "$LOG"
su -c "cmd wifi stop-softap"
sleep 2

# ================= APPLY BAND =================
echo "[LAN] Applying band..." >> "$LOG"

if [ "$BAND" = "2" ]; then
    su -c "cmd wifi force-softap-band enabled 2"
elif [ "$BAND" = "5" ]; then
    su -c "cmd wifi force-softap-band enabled 5"
else
    su -c "cmd wifi force-softap-band disabled"
fi

# ================= APPLY SECURITY =================
if [ "$SEC" = "open" ]; then
    CMD="cmd wifi start-softap \"$SSID\" open"
else
    [ "$SEC" = "wpa3" ] && SEC="wpa2"
    CMD="cmd wifi start-softap \"$SSID\" $SEC \"$PASS\""
fi

# ================= START HOTSPOT =================
echo "[LAN] Starting hotspot..." >> "$LOG"
su -c "$CMD"

sleep 3

echo "[LAN] Hotspot applied successfully" >> "$LOG"