FROM wordpress:6.9.1-php8.5-apache

COPY config/upload.ini $PHP_INI_DIR/conf.d/


# --- Tua logica esistente ---
WORKDIR /usr/src/wordpress
RUN set -eux; \
    find /etc/apache2 -name '*.conf' -type f \
      -exec sed -ri \
        -e "s!/var/www/html!$PWD!g" \
        -e "s!Directory /var/www/!Directory $PWD!g" '{}' +; \
    cp -s wp-config-docker.php wp-config.php

# --- SSH + CA + curl (per WP-CLI) ---
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      openssh-server \
      ca-certificates \
      curl \
      less \
      default-mysql-client \
 && mkdir -p /var/run/sshd \
 && rm -rf /var/lib/apt/lists/*

# Imposta la password di root come richiesto da Azure (user: root, password: Docker!)
RUN echo "root:Docker!" | chpasswd

# Copia la configurazione SSH speciale per Azure
COPY sshd_config /etc/ssh/sshd_config

RUN echo 'export WP_CLI_ALLOW_ROOT=1' >> /root/.bashrc \
 && echo 'cd /usr/src/wordpress' >> /root/.bashrc \
 && echo "alias wp='wp --path=/usr/src/wordpress'" >> /root/.bashrc

# --- WP-CLI (opzionale ma utile) ---
RUN curl -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
 && chmod +x /usr/local/bin/wp


COPY wp-content/plugins/ /usr/src/wordpress/wp-content/plugins/
COPY wp-content/themes/  /usr/src/wordpress/wp-content/themes/
COPY wp-content/themes/  /usr/src/wordpress/wp-content/themes/
COPY wp-config.php /usr/src/wordpress/wp-config.php
RUN chown -R www-data:www-data /usr/src/wordpress/wp-content

# Espone HTTP e SSH
EXPOSE 80 2222

# Script di avvio che lancia sshd + docker-entrypoint di WordPress
COPY scripts/init_container.sh /usr/local/bin/init_container.sh
RUN chmod +x /usr/local/bin/init_container.sh

# Usiamo il nostro entrypoint, che alla fine chiama quello originale di WordPress
ENTRYPOINT ["/usr/local/bin/init_container.sh"]