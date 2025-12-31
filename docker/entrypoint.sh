#!/bin/sh
#
# CronBeat container entrypoint
# - Verifies that the database directory exists and is owned by www-data recursively.
# - Exits with non-zero status if the requirement is not met.

set -eu

DB_DIR="/var/www/html/db"

err() {
  printf '%s\n' "$*" >&2
}

# If no command passed, default to running supervisord
if [ "$#" -eq 0 ]; then
  set -- /usr/bin/supervisord -c /etc/supervisord.conf
fi

# 1) Ensure the DB directory exists
if [ ! -d "$DB_DIR" ]; then
  err "[ERROR] Database directory not found: $DB_DIR"
  err "Create it or mount a volume at that path, then ensure it is owned by www-data:www-data."
  err "Example: mkdir -p db && chown -R www-data:www-data db"
  exit 1
fi

# 2) Verify ownership of the db directory itself
if ! find "$DB_DIR" -maxdepth 0 -user www-data -group www-data | grep -q "."; then
  err "[ERROR] $DB_DIR must be owned by www-data:www-data"
  err "Fix with: chown -R www-data:www-data $DB_DIR"
  exit 1
fi

# 3) Verify ownership recursively for all contents
if find "$DB_DIR" -mindepth 1 \( -not -user www-data -o -not -group www-data \) -print -quit | grep -q "."; then
  err "[ERROR] All files and subdirectories in $DB_DIR must be owned by www-data:www-data"
  # Show up to 5 offending paths to aid debugging
  err "Offending paths (first 5):"
  find "$DB_DIR" -mindepth 1 \( -not -user www-data -o -not -group www-data \) -print | head -n 5 >&2 || true
  err "Fix with: chown -R www-data:www-data $DB_DIR"
  exit 1
fi

exec "$@"
