<?php
// Comments in English only.

if (!defined('ABSPATH')) exit;

/**
 * Return the most reliable post ID for the current rendering context.
 *
 * - In frontend loops and templates, get_the_ID() typically works.
 * - In the block editor, ACF and WP can pass different context; we keep it simple.
 *
 * @param array|null $block Optional block array (ACF block render receives $block).
 * @return int Post ID or 0 if unknown.
 */
function sb_get_context_post_id(): int
{
    // 1) Frontend / loop context.
    $id = (int) get_the_ID();
    if ($id > 0) return $id;

    // 2) Editor context: sometimes global $post is set.
    global $post;
    if (!empty($post) && !empty($post->ID)) return (int) $post->ID;

    return 0;
}

/**
 * Read a field value from the given post ID.
 * Prefers ACF (get_field), falls back to post meta.
 *
 * @param string $field_name Meta key / ACF field name.
 * @param int $post_id
 * @return mixed
 */
function sb_get_field_value(string $field_name, int $post_id)
{
    if ($post_id <= 0 || $field_name === '') return null;

    if (function_exists('get_field')) {
        // get_field returns formatted value depending on field type/settings.
        $v = get_field($field_name, $post_id);
        if ($v !== null && $v !== false && $v !== '') return $v;
    }

    // Fallback: raw post meta.
    $meta = get_post_meta($post_id, $field_name, true);
    return ($meta === '') ? null : $meta;
}