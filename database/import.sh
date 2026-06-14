#!/bin/bash
#
# Imports database/schema.sql into the production MySQL database.
# Run automatically by cPanel on every deploy (see ../.cpanel.yml).
#
# ⚠️  This OVERWRITES the database with the contents of schema.sql.
#     It is safe ONLY while the site has no real data (test phase).
#     Before going live with real users/donations, switch to a
#     migration-based workflow instead (see database/README.md).
#
# Credentials are read from database/db.conf which lives OUTSIDE git
# (see .gitignore). Create it once on the server — see database/README.md.

set -euo pipefail

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DUMP="$DIR/schema.sql"
CONF="$DIR/db.conf"

if [ ! -f "$DUMP" ]; then
  echo "[db-import] No schema.sql found at $DUMP — skipping import."
  exit 0
fi

if [ ! -f "$CONF" ]; then
  echo "[db-import] No db.conf found at $CONF — skipping import."
  echo "[db-import] Create it on the server to enable automatic DB updates."
  exit 0
fi

# shellcheck disable=SC1090
source "$CONF"

echo "[db-import] Importing $DUMP into database '$DB_NAME' ..."
mysql --host="${DB_HOST:-localhost}" \
      --user="$DB_USER" \
      --password="$DB_PASS" \
      "$DB_NAME" < "$DUMP"

echo "[db-import] Done."
