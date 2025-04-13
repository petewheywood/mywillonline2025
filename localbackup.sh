#!/bin/bash
# filepath: /Users/pete/Dev/mywillonline/localbackup.sh

# Configuration
SOURCE_DIR="/Users/pete/Dev/mywillonline"
BACKUP_DIR='/Volumes/Data/My Documents/Sites/mywillonline'
EXCLUDE_FILE="$SOURCE_DIR/deploy-exclude.txt"

echo "Starting local backup to $BACKUP_DIR"
echo "===========================================" 

# Check if backup volume is mounted
if [ ! -d "/Volumes/Data" ]; then
  echo "Error: Backup volume not mounted. Please connect your Data volume and try again."
  exit 1
fi

# Get list of files that would be transferred
echo "Checking differences..."
diff_output=$(rsync -anzcvi --delete \
  --no-perms \
  --no-times \
  --no-group \
  --exclude-from="$EXCLUDE_FILE" \
  "$SOURCE_DIR/" "$BACKUP_DIR/")

# Extract both files to be added/updated and files to be deleted
echo "Preparing file list..."
# Files to be added or updated (>f entries)
add_update_files=$(echo "$diff_output" | grep -E "^>f|^>\\.f" | sed 's/^[^[:space:]]*[[:space:]]*//' | sort)
# Files to be deleted (*deleting entries)
delete_files=$(echo "$diff_output" | grep "^*deleting" | sed 's/^*deleting //' | sort)

# Combine all changes into one list
all_changes=$(echo -e "$add_update_files\n$delete_files" | grep -v "^$")

# Count files that would be transferred
if [ -z "$all_changes" ]; then
  file_count=0
else
  file_count=$(echo "$all_changes" | wc -l | xargs)
fi

if [ "$file_count" -eq "0" ]; then
  echo "No files to backup - backup is already up to date!"
else
  # Show files to be added/updated
  if [ ! -z "$add_update_files" ]; then
    add_count=$(echo "$add_update_files" | grep -v "^$" | wc -l | xargs)
    echo "Files to be added/updated ($add_count):"
    echo "$add_update_files" | sed 's/^/  + /' | head -10
    if [ "$add_count" -gt 10 ]; then
      echo "  + ... and $((add_count - 10)) more files"
    fi
  fi
  
  # Show files to be deleted
  if [ ! -z "$delete_files" ]; then
    del_count=$(echo "$delete_files" | grep -v "^$" | wc -l | xargs)
    echo "Files to be deleted ($del_count):"
    echo "$delete_files" | sed 's/^/  - /' | head -10
    if [ "$del_count" -gt 10 ]; then
      echo "  - ... and $((del_count - 10)) more files"
    fi
  fi
  
  # Prompt for confirmation
  read -p "Continue with local backup? [y/N] " response
  
  if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
    echo "Backing up files..."
    
    # Execute the rsync command with the delete flag
    rsync -azcvi --delete \
      --no-perms \
      --no-times \
      --no-group \
      --omit-dir-times \
      --exclude-from="$EXCLUDE_FILE" \
      "$SOURCE_DIR/" "$BACKUP_DIR/"
      
    echo "Backup completed!"
  else
    echo "Backup cancelled."
  fi
fi

echo "Done!"