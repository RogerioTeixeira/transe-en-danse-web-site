<?php
// Comments in English only.

if (!defined('ABSPATH')) exit;



/**
 * Populate your select with ALL date fields from ALL custom post types.
 * Change "source_field" to your real ACF field name.
 */
add_filter('acf/load_field/name=source_field', function ($field) {
    // Comments in English only.

    $field['choices'] = [];
    $field['choices'][''] = '— Select a date field (any custom post type) —';

    $allowed_types = ['date_picker', 'date_time_picker'];

    $choices = sb_list_all_acf_date_fields_all_custom_post_types($allowed_types);
    foreach ($choices as $value => $label) {
        $field['choices'][$value] = $label;
    }

    return $field;
});

if (!defined('ABSPATH')) exit;

/**
 * Populate your select with ALL repeater fields from ALL field groups.
 * Change "source_repeater" to your real ACF field name.
 */
add_filter('acf/load_field/name=source_repeater', function ($field) {
    // Comments in English only.

    static $is_loading = false;
    static $cached_choices = null;

    // Prevent infinite recursion when ACF internally loads fields.
    if ($is_loading) {
        return $field;
    }

    // Compute only once per request.
    if (is_array($cached_choices)) {
        $field['choices'] = $cached_choices;
        return $field;
    }

    $is_loading = true;

    try {
        $raw = sb_list_all_acf_repeater_fields_all_groups();

        $field['choices'] = [];
        $field['choices'][''] = '— Select a repeater field —';

        foreach ($raw as $field_key => $label) {
            // Convert field key -> field name
            $def = function_exists('acf_get_field') ? acf_get_field($field_key) : null;
            if (!$def || empty($def['name'])) {
                continue;
            }

            // VALUE = field NAME (what you want)
            $field['choices'][ $def['name'] ] = $label;
        }

        $cached_choices = $field['choices'];
    } finally {
        $is_loading = false;
    }

    return $field;
});