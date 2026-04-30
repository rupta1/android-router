#!/system/bin/sh

ACTIVE="/data/data/com.termux/files/home/router/active_users.txt"
USAGE="/data/data/com.termux/files/home/router/public/user_usage.txt"
BASE="/data/data/com.termux/files/home/router/public"

while true
do
    if [ -f "$ACTIVE" ]; then

        TMP_USERS="/data/data/com.termux/files/home/router/tmp_users.txt"
        > "$TMP_USERS"

        # ================= LOOP DEVICES =================
        while IFS= read -r line
        do
            [ -z "$line" ] && continue

            IFS=':' read -r IP USER LIMIT <<< "$line"

            [ -z "$IP" ] && continue
            [ -z "$USER" ] && continue

            CHAIN="USER_${IP//./_}"

            # ===== CURRENT BYTES =====
            CURRENT=$(iptables -L "$CHAIN" -v -x 2>/dev/null | awk '/^[ ]*[0-9]+[ ]+[0-9]+/ {sum += $2} END {print sum}')
            [ -z "$CURRENT" ] && CURRENT=0

            # ===== DATA DELTA =====
            TMP_FILE="$BASE/tmp_$IP.txt"

            if [ -f "$TMP_FILE" ]; then
                LAST=$(cat "$TMP_FILE")
            else
                LAST=$CURRENT
            fi

            DELTA=$((CURRENT - LAST))
            [ "$DELTA" -lt 0 ] && DELTA=0

            echo "$CURRENT" > "$TMP_FILE"

            # ===== TIME DELTA (FIXED) =====
            TIME_FILE="$BASE/time_$IP.txt"
            NOW=$(date +%s)

            if [ -f "$TIME_FILE" ]; then
                LAST_TIME=$(cat "$TIME_FILE")
            else
                LAST_TIME=$NOW
            fi

            # ?? ONLY COUNT TIME WHEN TRAFFIC EXISTS
            if [ "$DELTA" -gt 0 ]; then
                TIME_DELTA=$((NOW - LAST_TIME))

		# prevent abnormal spike (logout race fix)
		if [ "$TIME_DELTA" -gt 5 ]; then
    			TIME_DELTA=0
		fi
            else
                TIME_DELTA=0
            fi

            [ "$TIME_DELTA" -lt 0 ] && TIME_DELTA=0

            echo "$NOW" > "$TIME_FILE"

            # ===== STORE TEMP =====
            echo "$USER:$DELTA:$TIME_DELTA:$LIMIT:$IP" >> "$TMP_USERS"

        done < "$ACTIVE"


        # ================= AGGREGATE PER USER =================
        USERS=$(cut -d: -f1 "$TMP_USERS" | sort | uniq)

        for U in $USERS
        do
            USER_DATA=$(grep "^$U:" "$TMP_USERS")

            SUM_DATA=0
            SUM_TIME=0
            LIMIT="unlimited"

            for row in $USER_DATA
            do
                D=$(echo "$row" | cut -d: -f2)
                T=$(echo "$row" | cut -d: -f3)
                L=$(echo "$row" | cut -d: -f4)
                IP=$(echo "$row" | cut -d: -f5)

                SUM_DATA=$((SUM_DATA + D))
                SUM_TIME=$((SUM_TIME + T))
                LIMIT=$L
            done

            # ===== OLD USAGE =====
            OLD_LINE=$(grep "^${U}:" "$USAGE" 2>/dev/null)

            OLD_DATA=$(echo "$OLD_LINE" | cut -d: -f2)
            OLD_TIME=$(echo "$OLD_LINE" | cut -d: -f3)

            [ -z "$OLD_DATA" ] && OLD_DATA=0
            [ -z "$OLD_TIME" ] && OLD_TIME=0

            TOTAL_DATA=$((OLD_DATA + SUM_DATA))
            TOTAL_TIME=$((OLD_TIME + SUM_TIME))

            # ===== SAVE =====
            sed -i "/^${U}:/d" "$USAGE"
            echo "${U}:${TOTAL_DATA}:${TOTAL_TIME}" >> "$USAGE"

            # ===== LIMIT CHECK =====
            if [ "$LIMIT" != "unlimited" ]; then

                LIMIT_BYTES=$((LIMIT * 1024 * 1024))

                if [ "$TOTAL_DATA" -ge "$LIMIT_BYTES" ]; then

                    grep "^$U:" "$TMP_USERS" | while read line2
                    do
                        IP2=$(echo "$line2" | cut -d: -f5)
                        /data/data/com.termux/files/home/router/block_ip.sh "$IP2"
                        sed -i "/$IP2/d" "$ACTIVE"
                    done
                fi
            fi

        done

        rm -f "$TMP_USERS"
    fi

    sleep 2
done