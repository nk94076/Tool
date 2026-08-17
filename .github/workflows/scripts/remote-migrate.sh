#!/usr/bin/env bash
# Run on the remote host via: ssh ... "bash -s -- '<deploy_path>'" < this-file
# Applies DB migrations and re-runs the idempotent seeder.
set -euo pipefail

APP_DIR="$1"
cd "$APP_DIR"

PHP_BIN="$(command -v php || echo /usr/bin/php)"
echo "Using PHP: $PHP_BIN ($("$PHP_BIN" -v | head -1))"

"$PHP_BIN" database/migrate.php
"$PHP_BIN" database/seeders/seed.php
