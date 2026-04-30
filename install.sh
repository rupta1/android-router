#!/system/bin/sh

# ============================================
# 📡 Android Router Installer
# 👨‍💻 Developed By: Rupta
# ============================================

# ---------- COLORS ----------
RED=$(tput setaf 1)
GREEN=$(tput setaf 2)
YELLOW=$(tput setaf 3)
BLUE=$(tput setaf 4)
CYAN=$(tput setaf 6)
RESET=$(tput sgr0)

# ---------- SPINNER ----------
spin() {
    pid=$!
    spin='-\|/'
    i=0
    while kill -0 $pid 2>/dev/null; do
        i=$(( (i+1) %4 ))
        printf "\r${CYAN}[%c]${RESET} $1..." "${spin:$i:1}"
        sleep .1
    done
    wait $pid
    printf "\r${GREEN}[✔]${RESET} $1 completed\n"
}

# ---------- HEADER ----------
clear
echo "${BLUE}"
echo "============================================"
echo "        📡 Android Router Installer"
echo "--------------------------------------------"
echo "        👨‍💻 Developed By: Rupta"
echo "============================================"
echo "${RESET}"

# ---------- PATH ----------
export PREFIX=/data/data/com.termux/files/usr
export PATH=$PREFIX/bin:$PATH

HOME_DIR="/data/data/com.termux/files/home"
SRC_DIR="$(pwd)"

# ---------- STEP 1 ----------
echo "${YELLOW}[1/5] Installing dependencies${RESET}"
(
pkg update -y >/dev/null 2>&1
pkg upgrade -y >/dev/null 2>&1
pkg install -y root-repo sudo dnsmasq iptables php iproute2 tsu coreutils zip >/dev/null 2>&1
) &
spin "Installing packages"

# ---------- STEP 2 ----------
echo "${YELLOW}[2/5] Checking root access${RESET}"
(
su -c "id" >/dev/null 2>&1
) &
spin "Verifying root"

su -c "id" >/dev/null 2>&1 || {
    echo "${RED}❌ Root access not available${RESET}"
    exit 1
}

# ---------- STEP 3 ----------
echo "${YELLOW}[3/5] Deploying files${RESET}"
(
cp -r "$SRC_DIR/router" "$HOME_DIR/" 2>/dev/null
cp "$SRC_DIR/start-router.sh" "$HOME_DIR/"
cp "$SRC_DIR/stop-router.sh" "$HOME_DIR/"
cp "$SRC_DIR/dnsmasq.conf" "$HOME_DIR/"
) &
spin "Copying router files"

# ---------- STEP 4 ----------
echo "${YELLOW}[4/5] Setting permissions${RESET}"
(
chmod +x "$HOME_DIR/start-router.sh"
chmod +x "$HOME_DIR/stop-router.sh"
chmod +x "$HOME_DIR/router/"*.sh
) &
spin "Applying permissions"

# ---------- STEP 5 ----------
echo "${YELLOW}[5/5] Initializing system${RESET}"
(
mkdir -p "$HOME_DIR/router/public/backup"
mkdir -p "$HOME_DIR/router/public/data"

touch "$HOME_DIR/router/active_users.txt"
touch "$HOME_DIR/router/public/user_usage.txt"
touch "$HOME_DIR/router/public/logs.txt"

# default admin
[ ! -f "$HOME_DIR/router/public/admin_db.txt" ] && \
echo "admin:admin" > "$HOME_DIR/router/public/admin_db.txt"

# default user
[ ! -f "$HOME_DIR/router/public/users_db.txt" ] && \
echo "user1:1234:2mbit:60:500" > "$HOME_DIR/router/public/users_db.txt"

) &
spin "Creating system files"

# ---------- DONE ----------
echo ""
echo "${GREEN}============================================"
echo "        ✅ INSTALLATION COMPLETE"
echo "============================================${RESET}"
echo ""
echo "${CYAN}📌 Start Router:${RESET}"
echo "   su -c ~/start-router.sh"
echo ""
echo "${CYAN}🌐 Open Portal:${RESET}"
echo "   http://<hotspot-ip>"
echo ""
echo "${BLUE}--------------------------------------------"
echo "👨‍💻 Developed By: Rupta"
echo "============================================${RESET}"
echo ""