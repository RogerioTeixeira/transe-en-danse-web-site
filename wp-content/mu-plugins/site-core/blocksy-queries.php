<?php
if (!defined('ABSPATH')) exit;

/**
 * True if there is at least one upcoming performance (event_date >= now).
 * Runs once per request.
 */
function site_core_has_upcoming_performances(): bool {
  static $has = null;
  if ($has !== null) return $has;

  $now = current_time('mysql');

  $q = new WP_Query([
    'post_type'      => 'performances',
    'post_status'    => 'publish',
    'posts_per_page' => 1,
    'fields'         => 'ids',
    'no_found_rows'  => true,
    'meta_query'     => [[
      'key'     => 'event_date',
      'value'   => $now,
      'compare' => '>=',
      'type'    => 'DATETIME',
    ]],
  ]);

  $has = $q->have_posts();
  wp_reset_postdata();
  return $has;
}

add_filter('blocksy:general:blocks:query:args', function ($query_args, $attributes) {

  if (($attributes['uniqueId'] ?? '') !== '50f9f2c8') {
    return $query_args;
  }

  $now = current_time('mysql');
  $show_upcoming = site_core_has_upcoming_performances();

  // Base: always query performances, order by event_date
  $args = [
    'post_type'      => 'performances',
    'post_status'    => 'publish',
    'posts_per_page' => $show_upcoming ? 6 : 3,

    'meta_query' => [[
      'key'     => 'event_date',
      'value'   => $now,
      'compare' => $show_upcoming ? '>=' : '<',
      'type'    => 'DATETIME',
    ]],

    'meta_key'   => 'event_date',
    'orderby'    => 'meta_value',
    'meta_type'  => 'DATETIME',
    'order'      => $show_upcoming ? 'ASC' : 'DESC',
  ];

  return $args;

}, 999, 2);