FROM wordpress:6.9.0-php8.5-apache


WORKDIR /usr/src/wordpress
RUN set -eux; \
    find /etc/apache2 -name '*.conf' -type f \
      -exec sed -ri \
        -e "s!/var/www/html!$PWD!g" \
        -e "s!Directory /var/www/!Directory $PWD!g" '{}' +; \
    cp -s wp-config-docker.php wp-config.php

# --- SSH + CA + mysql-client ---
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      openssh-server \
      ca-certificates \
      mysql-client \
 && mkdir -p /var/run/sshd \
 && rm -rf /var/lib/apt/lists/*

# --- WP-CLI ---
RUN curl -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
 && chmod +x /usr/local/bin/wp

# Espone HTTP e SSH
EXPOSE 80 2222

# Script di start che avvia sshd + Apache/WordPress
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
