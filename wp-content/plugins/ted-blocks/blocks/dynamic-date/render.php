<?php
// Comments in English only.

if (!defined('ABSPATH')) exit;

if (!function_exists('sb_normalize_source_field_name')) {
    function sb_normalize_source_field_name(string $source_field): string
    {
        $source_field = trim($source_field);
        if ($source_field === '') return '';

        // If value is "posttype:field_name", keep only "field_name"
        $pos = strpos($source_field, ':');
        if ($pos !== false) {
            $source_field = substr($source_field, $pos + 1);
        }

        return sanitize_key($source_field);
    }
}

$post_id = sb_get_context_post_id();

$source_field = (string) get_field('source_field');
$source_field = sb_normalize_source_field_name($source_field);

$format        = get_field('format') ?: 'j F Y';
$custom_format = get_field('custom_format') ?: '';
$hide_if_empty = (bool) get_field('hide_if_empty');

if (!$post_id || !$source_field) return;

$raw = sb_get_field_value($source_field, $post_id);

if ($raw === null || $raw === '') {
    if ($hide_if_empty) return;
    echo '<span></span>';
    return;
}

$ts = null;

if (is_string($raw)) {
    $v = trim($raw);

    // Handle ACF date_picker default storage (Ymd)
    if (preg_match('/^\d{8}$/', $v)) {
        $dt = DateTime::createFromFormat('Ymd', $v);
        if ($dt) $ts = $dt->getTimestamp();
    }

    if ($ts === null) {
        $tmp = strtotime($v);
        if ($tmp !== false) $ts = $tmp;
    }
} elseif ($raw instanceof DateTimeInterface) {
    $ts = $raw->getTimestamp();
}

if ($ts === null) {
    echo esc_html(is_string($raw) ? $raw : '');
    return;
}

$attrs = get_block_wrapper_attributes();

if ($format === 'custom' && $custom_format) {
    $format = $custom_format;
}

echo '<div ' . $attrs . '>';
echo '<time datetime="' . esc_attr(wp_date('c', $ts)) . '">';
echo esc_html(wp_date($format, $ts));
echo '</time>';
echo '</div>';