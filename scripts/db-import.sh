#!/bin/bash

# ===== CONFIG =====
CONTAINER_NAME="wordpress"
DB_NAME="wordpress"
DB_USER="root"
DB_PASSWORD="password"

if [ -z "$1" ]; then
  echo "Usage: ./db-import.sh file.sql or file.sql.gz"
  exit 1
fi

SQL_FILE="$1"

if [ ! -f "$SQL_FILE" ]; then
  echo "File not found: $SQL_FILE"
  exit 1
fi

echo "Importing $SQL_FILE into $DB_NAME..."

if [[ "$SQL_FILE" == *.gz ]]; then
  gunzip -c "$SQL_FILE" | docker exec -i $CONTAINER_NAME \
    mysql -hdb -u$DB_USER -p$DB_PASSWORD $DB_NAME
else
  cat "$SQL_FILE" | docker exec -i $CONTAINER_NAME \
    mysql -hdb -u$DB_USER -p$DB_PASSWORD $DB_NAME
fi

if [ $? -eq 0 ]; then
  echo "Import completed successfully."
else
  echo "Import failed!"
fi