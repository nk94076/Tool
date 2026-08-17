#!/usr/bin/env bash
# Run on the remote host via: ssh ... "bash -s -- '<deploy_path>'" < this-file
# Symlinks the hPanel-managed web root to <deploy_path>/public (idempotent)
# and sets writable permissions on storage/ and public/uploads/.
set -euo pipefail

APP_DIR="$1"
WEB_ROOT="$(dirname "$APP_DIR")/public_html/employee"

if [ -L "$WEB_ROOT" ] && [ "$(readlink -f "$WEB_ROOT")" = "$(readlink -f "$APP_DIR/public")" ]; then
  echo "Web root already linked correctly: $WEB_ROOT -> $APP_DIR/public"
else
  rm -rf "$WEB_ROOT"
  ln -s "$APP_DIR/public" "$WEB_ROOT"
  echo "Linked $WEB_ROOT -> $APP_DIR/public"
fi

chmod -R 755 "$APP_DIR/storage" "$APP_DIR/public/uploads"
echo "Permissions set."
