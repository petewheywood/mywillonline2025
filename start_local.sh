#!/bin/bash
# start_local.sh
#
# Starts the local Docker dev site (db + web) and waits until it's actually
# ready to serve, rather than just "containers running".
#
# Usage:
#   ./start_local.sh          # start (only builds the image if it doesn't exist yet)
#   ./start_local.sh --build  # force a rebuild of the web image (e.g. after Dockerfile changes)

set -euo pipefail

LOCAL_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$LOCAL_DIR"

DB_HEALTH_TIMEOUT=120   # seconds to wait for MySQL healthcheck
SITE_READY_TIMEOUT=60   # seconds to wait for the site to stop showing maintenance

BUILD_FLAG=""
if [[ "${1:-}" == "--build" ]]; then
  BUILD_FLAG="--build"
fi

echo "--> Starting containers..."
docker compose up -d $BUILD_FLAG

echo "--> Waiting for db to become healthy (timeout ${DB_HEALTH_TIMEOUT}s)..."
elapsed=0
until [ "$(docker compose ps -q db | xargs -r docker inspect -f '{{.State.Health.Status}}' 2>/dev/null)" = "healthy" ]; do
  if [ "$elapsed" -ge "$DB_HEALTH_TIMEOUT" ]; then
    echo ""
    echo "ERROR: db did not become healthy in time. Check: docker compose logs db"
    exit 1
  fi
  printf '.'
  sleep 2
  elapsed=$((elapsed + 2))
done
echo " ready"

# MySQL can briefly restart (finishing an import / switching to full networking)
# just after the healthcheck first passes, which makes the app's DB connection
# fail and fall back to the maintenance page for a few seconds. Wait it out.
echo "--> Waiting for the site to come up (timeout ${SITE_READY_TIMEOUT}s)..."
elapsed=0
until page=$(curl -sf http://localhost:8080/ 2>/dev/null) && ! grep -q "undergoing maintenance" <<< "$page"; do
  if [ "$elapsed" -ge "$SITE_READY_TIMEOUT" ]; then
    echo ""
    echo "WARNING: site still showing the maintenance page after ${SITE_READY_TIMEOUT}s."
    echo "Check: docker compose logs web"
    exit 0
  fi
  printf '.'
  sleep 2
  elapsed=$((elapsed + 2))
done
echo " ready"

echo ""
echo "==========================================="
echo "Site: http://localhost:8080"
echo "==========================================="
