#!/usr/bin/env bash
# Run on the remote host via: ssh ... "bash -s -- '<deploy_path>'" < this-file
# Installs the portal's crontab entries once, idempotently (checks for a
# marker comment before appending so re-deploys never duplicate entries).
set -euo pipefail

APP_DIR="$1"
PHP_BIN="$(command -v php || echo /usr/bin/php)"
MARKER="# adhook-portal-cron"

if crontab -l 2>/dev/null | grep -qF "$MARKER"; then
  echo "Cron jobs already installed, skipping."
  exit 0
fi

NEW_CRON=$(cat <<CRON
$MARKER
0 6 * * * $PHP_BIN $APP_DIR/cron/birthday_reminders.php >> $APP_DIR/storage/logs/cron.log 2>&1
5 6 * * * $PHP_BIN $APP_DIR/cron/birthday_notifications.php >> $APP_DIR/storage/logs/cron.log 2>&1
10 6 * * * $PHP_BIN $APP_DIR/cron/anniversary_reminders.php >> $APP_DIR/storage/logs/cron.log 2>&1
15 6 * * * $PHP_BIN $APP_DIR/cron/anniversary_notifications.php >> $APP_DIR/storage/logs/cron.log 2>&1
* * * * * $PHP_BIN $APP_DIR/cron/email_queue_worker.php >> $APP_DIR/storage/logs/cron.log 2>&1
30 2 * * * $PHP_BIN $APP_DIR/cron/cleanup_expired_otp.php >> $APP_DIR/storage/logs/cron.log 2>&1
35 2 * * * $PHP_BIN $APP_DIR/cron/expire_secret_santa_access.php >> $APP_DIR/storage/logs/cron.log 2>&1
40 2 * * * $PHP_BIN $APP_DIR/cron/cleanup_stale_data.php >> $APP_DIR/storage/logs/cron.log 2>&1
CRON
)

(crontab -l 2>/dev/null; echo "$NEW_CRON") | crontab -
echo "Cron jobs installed."
