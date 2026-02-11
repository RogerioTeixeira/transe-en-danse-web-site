<?php
/**
 * The base configuration for WordPress
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// a helper function to lookup "env_FILE", "env", then fallback
if (!function_exists('getenv_docker')) {
    function getenv_docker($env, $default = null) {
        if ($fileEnv = getenv($env . '_FILE')) {
            return rtrim(file_get_contents($fileEnv), "\r\n");
        } elseif (($val = getenv($env)) !== false) {
            return $val;
        } else {
            return $default;
        }
    }
}

/** ===============================
 *  DATABASE CONFIG
 *  =============================== */
define('DB_NAME', getenv_docker('WORDPRESS_DB_NAME', 'wordpress'));
define('DB_USER', getenv_docker('WORDPRESS_DB_USER', 'example username'));
define('DB_PASSWORD', getenv_docker('WORDPRESS_DB_PASSWORD', 'example password'));
define('DB_HOST', getenv_docker('WORDPRESS_DB_HOST', 'mysql'));
define('DB_CHARSET', getenv_docker('WORDPRESS_DB_CHARSET', 'utf8mb4'));
define('DB_COLLATE', getenv_docker('WORDPRESS_DB_COLLATE', ''));

/** ===============================
 *  AUTH KEYS & SALTS
 *  =============================== */
define('AUTH_KEY',         getenv_docker('WORDPRESS_AUTH_KEY',         'put your unique phrase here'));
define('SECURE_AUTH_KEY',  getenv_docker('WORDPRESS_SECURE_AUTH_KEY',  'put your unique phrase here'));
define('LOGGED_IN_KEY',    getenv_docker('WORDPRESS_LOGGED_IN_KEY',    'put your unique phrase here'));
define('NONCE_KEY',        getenv_docker('WORDPRESS_NONCE_KEY',        'put your unique phrase here'));
define('AUTH_SALT',        getenv_docker('WORDPRESS_AUTH_SALT',        'put your unique phrase here'));
define('SECURE_AUTH_SALT', getenv_docker('WORDPRESS_SECURE_AUTH_SALT', 'put your unique phrase here'));
define('LOGGED_IN_SALT',   getenv_docker('WORDPRESS_LOGGED_IN_SALT',   'put your unique phrase here'));
define('NONCE_SALT',       getenv_docker('WORDPRESS_NONCE_SALT',       'put your unique phrase here'));

/** ===============================
 *  CACHE
 *  =============================== */
define('WP_CACHE', true);

/** ===============================
 *  TABLE PREFIX
 *  =============================== */
$table_prefix = getenv_docker('WORDPRESS_TABLE_PREFIX', 'wp_');

/** ===============================
 *  DEBUG
 *  =============================== */
define('WP_DEBUG', !!getenv_docker('WORDPRESS_DEBUG', ''));
define('WP_DEBUG_DISPLAY', false);

/** ===============================
 *  ENV SWITCH (local / production)
 *  =============================== */
$env = getenv_docker('ENV', 'local');

/** ===============================
 *  Canonical Site URL
 *  =============================== */
if ($env === 'production') {

    $site_url = getenv_docker('WORDPRESS_SITE_URL');

    if (empty($site_url)) {
        die('WORDPRESS_SITE_URL missing in production');
    }

} else {

    // Local Docker fallback
    $site_url = 'http://localhost:8080';
}

define('WP_HOME', $site_url);
define('WP_SITEURL', $site_url);

/** ===============================
 *  Reverse proxy HTTPS (Azure only)
 *  =============================== */
if ($env === 'production') {

    if (
        isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
        strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false
    ) {
        $_SERVER['HTTPS'] = 'on';
    }

    if (isset($_SERVER['HTTP_X_FORWARDED_PORT'])) {
        $_SERVER['SERVER_PORT'] = 443;
    }

} else {

    // Local Docker: never force SSL
    define('FORCE_SSL_ADMIN', false);
    $_SERVER['HTTPS'] = 'off';
}

/** ===============================
 *  EXTRA CONFIG (Docker)
 *  =============================== */
if ($configExtra = getenv_docker('WORDPRESS_CONFIG_EXTRA', '')) {
    eval($configExtra);
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';