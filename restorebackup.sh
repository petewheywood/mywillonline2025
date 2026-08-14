#!/bin/bash
# Restore a backup to the remote /var/www folder

# Check parameter
if [ $# -eq 0 ]; then
    echo "Usage: $0 {dev|prod} [backup-folder-name]"
    echo "  dev  - Restore to development environment (/var/www/dev_mywillonline)"
    echo "  prod - Restore to production environment (/var/www/mywillonline)"
    echo ""
    echo "If no backup folder name is specified, available backups will be listed."
    exit 1
fi

ENVIRONMENT=$1
REMOTE_SERVER="mwoserver"

# Validate parameter
if [ "$ENVIRONMENT" != "dev" ] && [ "$ENVIRONMENT" != "prod" ]; then
    echo "Error: Invalid environment '$ENVIRONMENT'"
    echo "Usage: $0 {dev|prod} [backup-folder-name]"
    exit 1
fi

# Set remote directory based on environment
if [ "$ENVIRONMENT" = "dev" ]; then
    REMOTE_DIR="/var/www/dev_mywillonline"
elif [ "$ENVIRONMENT" = "prod" ]; then
    REMOTE_DIR="/var/www/mywillonline"
fi

BACKUP_BASE="~/backups"

# If no backup folder specified, list available backups
if [ $# -lt 2 ]; then
    echo "Available backups for '$ENVIRONMENT':"
    echo "==========================================="
    ssh $REMOTE_SERVER "ls -1dt $BACKUP_BASE/mywillonline-${ENVIRONMENT}-* 2>/dev/null | head -20" | while read dir; do
        echo "  $(basename $dir)"
    done
    echo ""
    echo "Usage: $0 $ENVIRONMENT <backup-folder-name>"
    exit 0
fi

BACKUP_NAME=$2
BACKUP_PATH="$BACKUP_BASE/$BACKUP_NAME"

# Verify the backup exists
echo "Checking backup exists..."
ssh $REMOTE_SERVER "test -d $BACKUP_PATH"
if [ $? -ne 0 ]; then
    echo "ERROR: Backup '$BACKUP_NAME' not found at $BACKUP_PATH"
    echo "Run '$0 $ENVIRONMENT' to list available backups."
    exit 1
fi

echo "==========================================="
echo "RESTORE OPERATION"
echo "==========================================="
echo "  Environment: $ENVIRONMENT"
echo "  Backup:      $BACKUP_PATH"
echo "  Target:      $REMOTE_DIR"
echo "==========================================="
echo ""
echo "WARNING: This will replace the contents of $REMOTE_DIR with the backup."
read -p "Are you sure? [y/N] " response

if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
    echo "Restoring backup..."
    ssh $REMOTE_SERVER "rm -rf $REMOTE_DIR/* && cp -a $BACKUP_PATH/. $REMOTE_DIR/"
    if [ $? -eq 0 ]; then
        echo "Restore completed successfully!"
    else
        echo "ERROR: Restore failed!"
        exit 1
    fi
else
    echo "Restore cancelled."
fi
