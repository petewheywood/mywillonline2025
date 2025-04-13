#!/bin/bash
# filepath: /Users/pete/Dev/mywillonline/deploy.sh

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
deploy-exclude.txt
info/
EOL
fi

echo "Starting deployment to $REMOTE_SERVER:$REMOTE_DIR"
echo "===========================================" 

# Create temporary directory for comparison
TEMP_DIR=$(mktemp -d)
echo "Created temporary directory: $TEMP_DIR"

# First, rsync from remote to temp to have files for comparison
echo "Fetching remote files for comparison..."
rsync -az --delete \
    --exclude-from="$EXCLUDE_FILE" \
    $REMOTE_SERVER:$REMOTE_DIR/ $TEMP_DIR/

# Compare local and temp (which is a copy of remote), and list differences
echo "Checking for differences..."
diff_files=$(rsync -anzcvi --delete \
  --exclude-from="$EXCLUDE_FILE" \
  $LOCAL_DIR/ $TEMP_DIR/ | grep -v "/$" | sed '1d')

# Count files that would be transferred
file_count=$(echo "$diff_files" | grep -v "^$" | wc -l | xargs)

if [ "$file_count" -eq "0" ]; then
  echo "No files to update!"
else
  echo "Files to be transferred: $file_count"
  echo "$diff_files"
  
  # Prompt for confirmation
  read -p "Continue with deployment? [y/N] " response
  
  if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
    echo "Deploying files..."
    rsync -azcvi --delete \
      --exclude-from="$EXCLUDE_FILE" \
      $LOCAL_DIR/ $REMOTE_USER@$REMOTE_SERVER:$REMOTE_DIR/
      
    echo "Deployment completed!"
  else
    echo "Deployment cancelled."
  fi
fi

# Clean up
echo "Cleaning up temporary files..."
rm -rf $TEMP_DIR
echo "Done!"