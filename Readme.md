# Android Router (Termux + Root)

A lightweight Android-based router system built on Termux, providing a captive portal, per-user bandwidth control, and centralized management through a web interface.

---

## Overview

This project turns a rooted Android device into a functional WiFi router with:

* Captive portal authentication
* Per-user bandwidth limiting (tc / HTB)
* Data and session time tracking
* Multi-device support
* Firewall controls (MAC / URL / proxy)
* Persistent hotspot configuration
* Web-based admin panel

The system relies on `iptables`, `tc`, and Android's SoftAP interface (`ap0`).

---

## Installation

### Quick Install

```bash
pkg install git -y
cd ~
git clone https://github.com/rupta1/android-router.git
cd android-router
chmod +x install.sh
./install.sh
```

---

## Runtime Structure

After installation, files are deployed into Termux HOME:

```
~/start-router.sh
~/stop-router.sh
~/router/
```

This layout is required. Scripts depend on absolute paths and will not work if relocated.

---

## Usage

### Start Router

```bash
su -c ~/start-router.sh
```

### Stop Router

```bash
su -c ~/stop-router.sh
```

---

## Access

Once running, open:

```
http://<hotspot-ip>
```

Example:

```
http://192.168.68.79
```

---

## Configuration

### Admin Credentials

File:

```
~/router/public/admin_db.txt
```

Default:

```
admin:admin@123
```

---

### User Database

File:

```
~/router/public/users_db.txt
```

Format:

```
username:password:speed:session:data
```

Example:

```
user1:1234:2mbit:60:500
```

---

## Core Components

### Networking

* `iptables` for NAT, filtering, and captive portal redirect
* `tc` (HTB) for bandwidth control
* IP forwarding enabled at runtime

### Captive Portal Flow

1. Client connects to hotspot
2. Traffic blocked by default
3. HTTP redirected to portal
4. User authenticates
5. Access granted via rule insertion

---

## Usage Tracking

Stored in:

```
~/router/public/user_usage.txt
```

Tracks:

* Total data usage (bytes)
* Active session time

---

## LAN Configuration

Stored in:

```
~/router/lan_config.txt
```

Applied automatically at router start.

Supports:

* SSID
* Password
* Security mode
* Band selection

---

## Firewall Features

* MAC address blocking
* URL filtering (per IP)
* Proxy routing

---

## Logs

```
~/router/public/logs.txt
```

Includes:

* Start/stop lifecycle
* Hotspot state changes
* Script output
* Errors

---

## Requirements

* Rooted Android device
* Termux environment
* Working hotspot interface (`ap0`)
* Kernel support for `iptables` and `tc`

---

## Troubleshooting

### No Internet Access

```bash
echo 1 > /proc/sys/net/ipv4/ip_forward
```

---

### Captive Portal Not Redirecting

```bash
iptables -t nat -L
```

---

### Port 80 Conflict

```bash
su -c ~/stop-router.sh
sleep 10
su -c ~/start-router.sh
```

---

## Notes

* This system runs with root privileges.
* Not intended for public internet exposure without additional hardening.
* Designed for local/private network control.

---

## Author

Rupta
