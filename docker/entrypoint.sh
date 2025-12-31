#!/bin/sh

set -eu

DB_DIR="/var/www/html/db"

err() {
  printf '%s\n' "$*" >&2
}

log() {
  printf '%s\n' "$*"
}

if [ "$#" -eq 0 ]; then
  set -- /usr/bin/supervisord -c /etc/supervisord.conf
fi

# Check ownership; if anything is not owned by www-data, fix it
if [ -d "$DB_DIR" ]; then
  needs_fix=0
  if ! find "$DB_DIR" -maxdepth 0 -user www-data -group www-data 2>/dev/null | grep -q "."; then
    needs_fix=1
  fi
  if [ "$needs_fix" -eq 0 ] && find "$DB_DIR" -mindepth 1 \( -not -user www-data -o -not -group www-data \) -print -quit 2>/dev/null | grep -q "."; then
    needs_fix=1
  fi

  if [ "$needs_fix" -eq 1 ]; then
    log "[INIT] Fixing ownership: chown -R www-data:www-data $DB_DIR"
    chown -R www-data:www-data "$DB_DIR"
  fi
fi

exec "$@"
