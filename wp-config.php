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
    function getenv_docker($env, $default) {
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
 *  ACTIVATE CHACHE
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

/** ======================================================
 *  🔥 AZURE APP SERVICE / REVERSE PROXY FIX (CRITICO)
 *  ====================================================== */

// Canonical URL (blocca redirect strani)
define('WP_HOME', 'https://staging.transe-en-danse.org');
define('WP_SITEURL', 'https://staging.transe-en-danse.org');

// HTTPS dietro reverse proxy
if (
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
    strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false
) {
    $_SERVER['HTTPS'] = 'on';
}

// 🔥 FIX PORT (8080 → 443)
if (isset($_SERVER['HTTP_X_FORWARDED_PORT'])) {
    $_SERVER['SERVER_PORT'] = 443;
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