#!/bin/bash
set -e

echo "Starting SSH server on port 2222 ..."
service ssh start

echo "Starting WordPress (Apache) ..."
# Passa il controllo all'entrypoint originale dell'immagine WordPress
exec /usr/local/bin/docker-entrypoint.sh apache2-foreground