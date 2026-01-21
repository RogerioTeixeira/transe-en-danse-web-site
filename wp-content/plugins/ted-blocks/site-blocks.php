<?php
/**
 * Plugin Name: TED Blocks
 * Description: Minimal custom blocks (ACF-powered) for this website.
 * Version: 0.1.0
 * Author: Rogerio
 */

if (!defined('ABSPATH')) exit;

define('SB_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once SB_PLUGIN_DIR . 'core/core.php';
require_once SB_PLUGIN_DIR . 'core/acf-fields.php';
require_once SB_PLUGIN_DIR . 'core/field-populators.php';
require_once SB_PLUGIN_DIR . 'core/acf-settings.php';

add_action('init', function () {
    // Register blocks from block.json metadata
    register_block_type_from_metadata(SB_PLUGIN_DIR . 'blocks/dynamic-date');
    register_block_type_from_metadata(SB_PLUGIN_DIR . 'blocks/acf-repeater');
});