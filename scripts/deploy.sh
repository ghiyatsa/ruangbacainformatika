#!/usr/bin/env bash
# Deploy ruangbacainformatika: pull, build client + SSR, restart SSR, clear cache.
# SSR bundle MUST be rebuilt AND the SSR process restarted on every deploy —
# otherwise the running process keeps the old bundle in memory and serves
# stale markup forever.
set -euo pipefail

APP_DIR="/www/wwwroot/ruangbacainformatika.ghiyatsa.web.id"
SSR_PORT=13714
SSR_LOG="/tmp/ssr.log"

cd "$APP_DIR"

chmod -R 777 .git/objects .git/refs .git/logs 2>/dev/null || true

sudo -u ubuntu -i git -C "$APP_DIR" fetch origin main
sudo git reset --hard FETCH_HEAD

# Client bundle + SSR bundle together
sudo -u www npm run build:ssr

# Remove first so the file is recreated owned by www (a root-owned leftover
# would make file_put_contents fail with permission denied).
sudo rm -f public/robots.txt
sudo -u www php artisan app:generate-robots
sudo -u www php artisan optimize
# --- Restart SSR -------------------------------------------------------------
# Kill EVERY matching process and verify the port is actually free before
# starting, otherwise the new process dies with EADDRINUSE and the stale
# old one keeps serving.
for pid in $(pgrep -f 'node bootstrap/ssr/ssr.js' || true); do
    sudo kill -9 "$pid" 2>/dev/null || true
done

for _ in $(seq 1 10); do
    if ss -tln 2>/dev/null | grep -q ":$SSR_PORT "; then
        sleep 1
    else
        break
    fi
done

if ss -tln 2>/dev/null | grep -q ":$SSR_PORT "; then
    echo "ERROR: port $SSR_PORT still occupied, cannot start SSR" >&2
    sudo fuser -v "$SSR_PORT/tcp" >&2 2>&1 || true
    exit 1
fi

sudo rm -f "$SSR_LOG"
sudo -u www bash -c "cd $APP_DIR && setsid nohup node bootstrap/ssr/ssr.js > $SSR_LOG 2>&1 < /dev/null &"
sleep 5

if ss -tln 2>/dev/null | grep -q ":$SSR_PORT "; then
    echo "SSR listening on $SSR_PORT"
else
    echo "ERROR: SSR failed to start; log:" >&2
    cat "$SSR_LOG" >&2
    exit 1
fi

git log --oneline -1
