#!/bin/bash
set -e

# Avvia SSH in background sulla porta 2222
/usr/sbin/sshd -D -p 2222 &

# Avvia il normale entrypoint di WordPress (Apache in foreground)
/usr/local/bin/docker-entrypoint.sh apache2-foreground
