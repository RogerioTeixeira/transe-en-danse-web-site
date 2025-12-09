#!/bin/bash
set -e

# Rende le env del processo principale disponibili alle sessioni SSH
ENV_FILE="/etc/profile.d/app_env.sh"

{
  echo "# Generated from container environment"

  # Esporta tutte le variabili utili per WP e DB + WP_CLI_ALLOW_ROOT
  env | awk -F= '/^(WORDPRESS_|WP_|MYSQL_|WP_CLI_)/ {
    # stampa export VAR="valore"
    gsub(/"/, "\\\"", $2);
    printf("export %s=\"%s\"\n", $1, $2);
  }'

  # Qualità di vita in SSH:
  # vai direttamente nella cartella di WordPress
  echo 'cd /usr/src/wordpress'
  # alias wp che punta sempre alla cartella giusta
  echo "alias wp='wp --path=/usr/src/wordpress'"

} > "$ENV_FILE"

echo "Starting SSH server on port 2222 ..."
service ssh start

echo "Starting WordPress (Apache) ..."
exec /usr/local/bin/docker-entrypoint.sh apache2-foreground