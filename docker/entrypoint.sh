#!/bin/sh
#
# CronBeat container entrypoint
# - Ensures the database directory exists and is owned by www-data recursively.
# - Fixes ownership on every container start (chown if needed), then starts the main process.

set -eu

DB_DIR="/var/www/html/db"

err() {
  printf '%s\n' "$*" >&2
}

log() {
  printf '%s\n' "$*"
}

# If no command passed, default to running supervisord
if [ "$#" -eq 0 ]; then
  set -- /usr/bin/supervisord -c /etc/supervisord.conf
fi

# 1) Ensure the DB directory exists
if [ ! -d "$DB_DIR" ]; then
  log "[INIT] Creating database directory: $DB_DIR"
  mkdir -p "$DB_DIR"
fi

# 2) Check ownership; if anything is not owned by www-data, fix it
needs_fix=0
if ! find "$DB_DIR" -maxdepth 0 -user www-data -group www-data | grep -q "."; then
  needs_fix=1
fi
if [ "$needs_fix" -eq 0 ] && find "$DB_DIR" -mindepth 1 \( -not -user www-data -o -not -group www-data \) -print -quit | grep -q "."; then
  needs_fix=1
fi

if [ "$needs_fix" -eq 1 ]; then
  log "[INIT] Fixing ownership: chown -R www-data:www-data $DB_DIR"
  chown -R www-data:www-data "$DB_DIR"
fi

exec "$@"
