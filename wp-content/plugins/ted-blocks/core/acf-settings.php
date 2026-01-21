<?php
if (!defined('ABSPATH')) exit;

add_filter('acf/settings/block_fields_position', fn () => 'side');