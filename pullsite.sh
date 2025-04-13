#!/bin/bash
# filepath: /Users/pete/Dev/mywillonline/pull.sh

# Configuration
LOCAL_DIR="/Users/pete/Dev/mywillonline"
REMOTE_SERVER="mwo"
REMOTE_DIR="/var/www/mywillonline"
EXCLUDE_FILE="$LOCAL_DIR/deploy-exclude.txt"

# Create exclude file if it doesn't exist
if [ ! -f "$EXCLUDE_FILE" ]; then
  echo "Creating exclude file..."
  cat > "$EXCLUDE_FILE" <<EOL
.git/
.gitignore
deploy.sh
pull.sh
deploy-exclude.txt
info/
EOL
fi

echo "Starting pull from $REMOTE_SERVER:$REMOTE_DIR to local directory"
echo "=============================================================="

# Show what files would be changed
echo "Files that would be changed:"
rsync -anzcvi --delete \
  --exclude-from="$EXCLUDE_FILE" \
  $REMOTE_SERVER:$REMOTE_DIR/ $LOCAL_DIR/ | grep -v "/$"

# Prompt for confirmation
read -p "Continue with pull operation? This will overwrite local files. [y/N] " response

if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
  echo "Pulling files from remote server..."
  rsync -azcvi --delete \
    --exclude-from="$EXCLUDE_FILE" \
    $REMOTE_SERVER:$REMOTE_DIR/ $LOCAL_DIR/
    
  echo "Pull operation completed!"
else
  echo "Pull operation cancelled."
fi

echo "Done!"