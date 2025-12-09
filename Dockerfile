FROM wordpress:6.9.0-php8.5-apache

WORKDIR /usr/src/wordpress
RUN set -eux; \
    find /etc/apache2 -name '*.conf' -type f \
      -exec sed -ri \
        -e "s!/var/www/html!$PWD!g" \
        -e "s!Directory /var/www/!Directory $PWD!g" '{}' +; \
    cp -s wp-config-docker.php wp-config.php

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      openssh-server \
      ca-certificates \
      curl \
 && mkdir -p /var/run/sshd \
 && rm -rf /var/lib/apt/lists/*

# Imposta password root come richiesto da Azure
RUN echo "root:Docker!" | chpasswd

# Copia la configurazione SSH richiesta da Azure
COPY sshd_config /etc/ssh/sshd_config

EXPOSE 80 2222

COPY init_container.sh /usr/local/bin/init_container.sh
RUN chmod +x /usr/local/bin/init_container.sh

ENTRYPOINT ["/usr/local/bin/init_container.sh"]
