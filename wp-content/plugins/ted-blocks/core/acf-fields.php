<?php
// Comments in English only.

if (!defined('ABSPATH')) exit;

if (!function_exists('sb_acf_all_field_groups')) {
    /**
     * Return all ACF field groups (DB + local).
     *
     * @return array<int,array>
     */
    function sb_acf_all_field_groups(): array
    {
        if (!function_exists('acf_get_field_groups')) return [];

        // Try DB groups
        $groups = acf_get_field_groups();
        if (is_array($groups) && !empty($groups)) {
            return $groups;
        }

        // Fallback: local groups (registered via PHP)
        if (function_exists('acf_get_local_field_groups')) {
            $local = acf_get_local_field_groups();
            if (is_array($local) && !empty($local)) {
                // Normalize format (local groups are keyed by group key sometimes)
                return array_values($local);
            }
        }

        return [];
    }
}

if (!function_exists('sb_extract_post_types_from_group_locations')) {
    /**
     * Extract post types from an ACF field group "location" rules.
     * Only handles rules like: Post Type == something
     *
     * @param array $group
     * @return string[]
     */
    function sb_extract_post_types_from_group_locations(array $group): array
    {
        $pts = [];

        if (empty($group['location']) || !is_array($group['location'])) {
            return $pts;
        }

        // location is array of OR groups, each contains AND rules
        foreach ($group['location'] as $or_group) {
            if (!is_array($or_group)) continue;

            foreach ($or_group as $rule) {
                if (!is_array($rule)) continue;

                $param = $rule['param'] ?? '';
                $op    = $rule['operator'] ?? '';
                $val   = $rule['value'] ?? '';

                if ($param !== 'post_type') continue;
                if ($op !== '==') continue;
                if (!is_string($val) || $val === '') continue;

                $pts[] = sanitize_key($val);
            }
        }

        return array_values(array_unique($pts));
    }
}

if (!function_exists('sb_is_custom_post_type')) {
    /**
     * Check if a post type is custom (not built-in).
     */
    function sb_is_custom_post_type(string $pt): bool
    {
        $obj = get_post_type_object($pt);
        if (!$obj) return false;
        return empty($obj->_builtin); // _builtin === false for CPTs
    }
}

if (!function_exists('sb_pt_label')) {
    function sb_pt_label(string $pt): string
    {
        $obj = get_post_type_object($pt);
        if ($obj && !empty($obj->labels) && !empty($obj->labels->name)) {
            return (string) $obj->labels->name;
        }
        if ($obj && !empty($obj->label)) {
            return (string) $obj->label;
        }
        return $pt;
    }
}

if (!function_exists('sb_list_all_acf_date_fields_all_custom_post_types')) {
    /**
     * List ALL ACF date fields across ALL custom post types, by scanning all field groups.
     *
     * Value format: "post_type:field_name"
     * Label format: "Post Type Label — Field Label (field_name)"
     *
     * @param string[] $allowed_types
     * @return array<string,string>
     */
    function sb_list_all_acf_date_fields_all_custom_post_types(array $allowed_types): array
    {
        if (!function_exists('acf_get_fields')) return [];

        $choices = [];
        $groups  = sb_acf_all_field_groups();
        if (!$groups) return $choices;

        foreach ($groups as $group) {
            if (empty($group['key'])) continue;

            $pts = sb_extract_post_types_from_group_locations($group);
            if (!$pts) continue;

            $fields = acf_get_fields($group['key']);
            if (!$fields) continue;

            foreach ($pts as $pt) {
                if ($pt === 'ct_content_block') continue;       // optional noise
                if (!sb_is_custom_post_type($pt)) continue;     // only CPTs

                $ptLabel = sb_pt_label($pt);

                foreach ($fields as $f) {
                    if (empty($f['name']) || empty($f['type'])) continue;
                    if (!in_array($f['type'], $allowed_types, true)) continue;

                    // Ignore nested subfields for now
                    if (!empty($f['sub_fields'])) continue;
                    if (($f['type'] ?? '') === 'repeater') continue;
                    if (($f['type'] ?? '') === 'flexible_content') continue;

                    $name  = (string) $f['name'];
                    $label = !empty($f['label']) ? (string) $f['label'] : $name;

                    $value = $pt . ':' . $name; // avoid collisions
                    $choices[$value] = $ptLabel . ' — ' . $label . ' (' . $name . ')';
                }
            }
        }

        uasort($choices, function ($a, $b) {
            return strcasecmp((string) $a, (string) $b);
        });

        return $choices;
    }
}


if (!function_exists('sb_collect_repeater_fields_recursive')) {
    /**
     * Recursively collect repeater fields from a fields array.
     * Includes nested repeaters and repeaters inside flexible content layouts.
     *
     * @param array<int,array> $fields
     * @return array<int,array{key:string,name:string,label:string}>
     */
    function sb_collect_repeater_fields_recursive(array $fields): array
    {
        $out = [];

        foreach ($fields as $f) {
            if (!is_array($f) || empty($f['type'])) continue;

            if (($f['type'] ?? '') === 'repeater') {
                $out[] = [
                    'key'   => (string) ($f['key'] ?? ''),
                    'name'  => (string) ($f['name'] ?? ''),
                    'label' => (string) ($f['label'] ?? ''),
                ];
            }

            // Repeater sub fields
            if (!empty($f['sub_fields']) && is_array($f['sub_fields'])) {
                $out = array_merge($out, sb_collect_repeater_fields_recursive($f['sub_fields']));
            }

            // Flexible content layouts -> sub fields
            if (($f['type'] ?? '') === 'flexible_content' && !empty($f['layouts']) && is_array($f['layouts'])) {
                foreach ($f['layouts'] as $layout) {
                    if (!empty($layout['sub_fields']) && is_array($layout['sub_fields'])) {
                        $out = array_merge($out, sb_collect_repeater_fields_recursive($layout['sub_fields']));
                    }
                }
            }
        }

        // Remove invalid entries
        $out = array_values(array_filter($out, function ($x) {
            return !empty($x['key']) && !empty($x['name']);
        }));

        return $out;
    }
}

if (!function_exists('sb_list_all_acf_repeater_fields_all_groups')) {
    /**
     * List ALL ACF repeater fields across ALL field groups (DB + local).
     *
     * Value format: repeater_field_key  (stable)
     * Label format: "Group Title — Repeater Label (repeater_name)"
     *
     * @return array<string,string>
     */
    function sb_list_all_acf_repeater_fields_all_groups(): array
    {
        if (!function_exists('acf_get_fields')) return [];

        $choices = [];
        $groups  = sb_acf_all_field_groups();
        if (!$groups) return $choices;

        foreach ($groups as $group) {
            if (empty($group['key'])) continue;

            $fields = acf_get_fields($group['key']);
            if (!$fields) continue;

            $repeaters = sb_collect_repeater_fields_recursive($fields);
            if (!$repeaters) continue;

            $groupTitle = !empty($group['title']) ? (string) $group['title'] : (string) $group['key'];

            foreach ($repeaters as $rep) {
                $name  = (string) $rep['name'];
                $label = !empty($rep['label']) ? (string) $rep['label'] : $name;
                $key   = (string) $rep['key'];

                $choices[$key] = $groupTitle . ' — ' . $label . ' (' . $name . ')';
            }
        }

        uasort($choices, function ($a, $b) {
            return strcasecmp((string) $a, (string) $b);
        });

        return $choices;
    }
}