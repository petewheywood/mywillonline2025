#!/bin/bash
# stop_local.sh
#
# Stops the local Docker dev site. The db volume is kept by default, so your
# restored data survives until you next run refresh-local-db.sh.
#
# Usage:
#   ./stop_local.sh        # stop containers, keep local db data
#   ./stop_local.sh -v     # stop containers AND wipe local db data

set -euo pipefail

LOCAL_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$LOCAL_DIR"

if [[ "${1:-}" == "-v" ]]; then
  echo "--> Stopping containers and wiping local db volume..."
  docker compose down -v
else
  echo "--> Stopping containers (local db data kept)..."
  docker compose down
fi

echo "Done."
