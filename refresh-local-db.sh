#!/bin/bash
# refresh-local-db.sh
#
# Pulls the latest production DB backup down from the server, restores it into
# a fresh local Docker MySQL, and starts the local site.
#
# WARNING:
#   - This downloads a REAL production database dump to your machine, including
#     user will/estate data (names, addresses, will contents, etc).
#   - This WIPES the local Docker `mysql_data` volume (local dev data only -
#     does not touch production).
#
# Usage:
#   ./refresh-local-db.sh        # asks for confirmation first
#   ./refresh-local-db.sh -y     # skip confirmation

set -euo pipefail

REMOTE_SERVER="mwoserver"
REMOTE_BACKUP="/home/ubuntu/data_backup.sql.gz"

LOCAL_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOCAL_GZ="$LOCAL_DIR/data_backup.sql.gz"
LOCAL_SQL="$LOCAL_DIR/data_backup.sql"        # filename docker-compose.yml expects
HEALTH_TIMEOUT=120                            # seconds to wait for MySQL to come up

cd "$LOCAL_DIR"

SKIP_CONFIRM=false
if [[ "${1:-}" == "-y" ]]; then
  SKIP_CONFIRM=true
fi

echo "==========================================="
echo "Refresh local DB from production"
echo "==========================================="
echo "This will:"
echo "  1. Download $REMOTE_SERVER:$REMOTE_BACKUP"
echo "  2. Stop local containers and WIPE the local db volume"
echo "  3. Restore the backup into a fresh local MySQL"
echo "  4. Start the local site"
echo ""
echo "NOTE: this pulls real user will/estate data onto your machine."
echo ""

if [ "$SKIP_CONFIRM" = false ]; then
  read -p "Continue? [y/N] " response
  if [[ ! "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
    echo "Cancelled."
    exit 1
  fi
fi

echo "--> Downloading latest backup from $REMOTE_SERVER..."
scp "$REMOTE_SERVER:$REMOTE_BACKUP" "$LOCAL_GZ"

echo "--> Decompressing into $(basename "$LOCAL_SQL")..."
rm -f "$LOCAL_SQL"
gunzip -c "$LOCAL_GZ" > "$LOCAL_SQL"
rm -f "$LOCAL_GZ"

echo "--> Stopping containers and wiping local db volume..."
docker compose down -v

echo "--> Starting local db (fresh volume will auto-import the backup)..."
docker compose up -d db

echo "--> Waiting for db to become healthy (timeout ${HEALTH_TIMEOUT}s)..."
elapsed=0
until [ "$(docker compose ps -q db | xargs -r docker inspect -f '{{.State.Health.Status}}' 2>/dev/null)" = "healthy" ]; do
  if [ "$elapsed" -ge "$HEALTH_TIMEOUT" ]; then
    echo ""
    echo "ERROR: db did not become healthy in time. Check: docker compose logs db"
    exit 1
  fi
  printf '.'
  sleep 2
  elapsed=$((elapsed + 2))
done
echo " ready"

echo "--> Verifying data imported..."
TABLE_COUNT=$(docker compose exec -T -e MYSQL_PWD=mwo_pass db mysql -u mwo_user -N -e \
  "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='mwo';")
echo "    $TABLE_COUNT tables found in 'mwo' database."
if [ "$TABLE_COUNT" -eq 0 ]; then
  echo "ERROR: no tables found - import may have failed. Check: docker compose logs db"
  exit 1
fi

echo "--> Starting web..."
docker compose up -d web

echo ""
echo "==========================================="
echo "Done! Site: http://localhost:8080"
echo "==========================================="
