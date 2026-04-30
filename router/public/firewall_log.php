<?php

// DNS queries log (if using dnsmasq)
$log = @file_get_contents("/data/data/com.termux/files/usr/var/log/dnsmasq.log");

echo $log ?: "No logs";