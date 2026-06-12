#!/usr/bin/env bash
# Deploy SurfPan to production (Hostinger).
#
# Server layout:
#   modules/ templates/  ->  domains/surfpan.com/
#   public/              ->  domains/surfpan.com/public_html/  (docroot)
#
# Never synced:
#   engine/            — framework core is managed on the server, not deployed
#   config/            — production credentials & BASE_URL live only on the server
#   database/ docs/ remotion/ tutorial_videos/ — dev-only, never belong on prod
#   upload dirs        — user avatars / org logos uploaded in production
#
# Usage:  ./deploy.sh [--dry-run]
set -euo pipefail

SSH_HOST="u704589896@45.84.207.193"
SSH_PORT="65002"
REMOTE_ROOT="domains/surfpan.com"

cd "$(dirname "$0")"

RSYNC_FLAGS=(-rlptz --itemize-changes
  --exclude='.DS_Store'
  --exclude='*.bak.php'
  --exclude='OLD_*')

if [[ "${1:-}" == "--dry-run" ]]; then
  RSYNC_FLAGS+=(--dry-run)
  echo "== DRY RUN — nothing will be uploaded =="
fi

if [[ -n "$(git status --porcelain)" ]]; then
  echo "WARNING: uncommitted changes in working tree — they will be deployed as-is."
fi
echo "Deploying: $(git log -1 --oneline)"
echo

echo "-- application code --"
rsync "${RSYNC_FLAGS[@]}" \
  --exclude='users/assets/images/avatars/' \
  --exclude='organizations/assets/images/org_avatars/' \
  -e "ssh -p ${SSH_PORT}" \
  modules templates "${SSH_HOST}:${REMOTE_ROOT}/"

echo "-- public assets (docroot) --"
rsync "${RSYNC_FLAGS[@]}" \
  -e "ssh -p ${SSH_PORT}" \
  public/ "${SSH_HOST}:${REMOTE_ROOT}/public_html/"

echo
echo "Done -> https://www.surfpan.com"
