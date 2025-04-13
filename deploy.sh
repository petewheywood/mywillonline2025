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
diff_output=$(rsync -anzcvi --delete \
  --exclude-from="$EXCLUDE_FILE" \
  --no-perms \
  --no-times \
  $LOCAL_DIR/ $TEMP_DIR/)

# Extract both files to be added/updated and files to be deleted
echo "Preparing file list..."
# Files to be added or updated (>f entries)
add_update_files=$(echo "$diff_output" | grep -E "^>f|^>\\.f" | sed 's/^[^[:space:]]*[[:space:]]*//' | sort)
# Files to be deleted (*deleting entries)
delete_files=$(echo "$diff_output" | grep "^*deleting" | sed 's/^*deleting //' | sort)

# Combine all changes into one list
all_changes=$(echo -e "$add_update_files\n$delete_files" | grep -v "^$")

# Count files that would be transferred (handle empty result properly)
if [ -z "$all_changes" ]; then
  file_count=0
else
  file_count=$(echo "$all_changes" | wc -l | xargs)
fi

if [ "$file_count" -eq "0" ]; then
  echo "No files to update!"
else
  # Show files to be added/updated
  if [ ! -z "$add_update_files" ]; then
    add_count=$(echo "$add_update_files" | grep -v "^$" | wc -l | xargs)
    echo "Files to be added/updated ($add_count):"
    echo "$add_update_files" | sed 's/^/  + /'
  fi
  
  # Show files to be deleted
  if [ ! -z "$delete_files" ]; then
    del_count=$(echo "$delete_files" | grep -v "^$" | wc -l | xargs)
    echo "Files to be deleted ($del_count):"
    echo "$delete_files" | sed 's/^/  - /'
  fi
  
  # Prompt for confirmation
  read -p "Continue with deployment? [y/N] " response
  
  if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
    echo "Deploying files..."
    rsync -azcvi --delete \
      --exclude-from="$EXCLUDE_FILE" \
      --no-perms \
      --no-times \
      --omit-dir-times \
      $LOCAL_DIR/ $REMOTE_SERVER:$REMOTE_DIR/      
    echo "Deployment completed!"
  else
    echo "Deployment cancelled."
  fi
fi

# Clean up
echo "Cleaning up temporary files..."
rm -rf $TEMP_DIR
echo "Done!"