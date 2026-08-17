#!/usr/bin/env bash
# Run on the remote host via: ssh ... "bash -s -- '<deploy_path>'" < this-file
# Installs the portal's crontab entries once, idempotently (checks for a
# marker comment before appending so re-deploys never duplicate entries).
#
# Some shared-hosting accounts (e.g. certain Hostinger plans) don't expose
# the `crontab` command over SSH at all — cron is managed exclusively
# through the hosting panel's own "Cron Jobs" UI instead. That's an
# environment limitation, not a deploy failure, so this script detects it
# and prints the exact lines to paste into that UI rather than failing the
# whole pipeline.
set -euo pipefail

APP_DIR="$1"
PHP_BIN="$(command -v php || echo /usr/bin/php)"
MARKER="# adhook-portal-cron"

CRON_LINES=$(cat <<CRON
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

if ! command -v crontab >/dev/null 2>&1; then
  echo "::warning::The 'crontab' command is not available over SSH on this host — cron was NOT installed automatically."
  echo "Add these jobs manually via your hosting panel's Cron Jobs UI (one job per line, PHP CLI as the command):"
  echo "$CRON_LINES"
  exit 0
fi

if crontab -l 2>/dev/null | grep -qF "$MARKER"; then
  echo "Cron jobs already installed, skipping."
  exit 0
fi

(crontab -l 2>/dev/null; echo "$MARKER"; echo "$CRON_LINES") | crontab -
echo "Cron jobs installed."
