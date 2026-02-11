#!/bin/bash

# ===== CONFIG =====
CONTAINER_NAME="wordpress"
DB_NAME="wordpress"
DB_USER="root"
DB_PASSWORD="password"
BACKUP_DIR="./db-backups"

# ===== LOGIC =====
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
FILENAME="$BACKUP_DIR/${DB_NAME}_$TIMESTAMP.sql"

mkdir -p $BACKUP_DIR

echo "Creating dump..."

docker exec $CONTAINER_NAME \
  mysqldump -hdb -u$DB_USER -p$DB_PASSWORD $DB_NAME > $FILENAME

if [ $? -eq 0 ]; then
  echo "Dump created: $FILENAME"
else
  echo "Dump failed!"
fi