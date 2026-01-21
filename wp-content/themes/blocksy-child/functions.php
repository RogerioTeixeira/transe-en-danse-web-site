<?php

if (!defined('WP_DEBUG')) {
  die('Direct access forbidden.');
}

add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
  // Child CSS
  $child_css = get_stylesheet_directory() . '/style.css';
  wp_enqueue_style(
    'child-style',
    get_stylesheet_directory_uri() . '/style.css',
    ['parent-style'],
    file_exists($child_css) ? filemtime($child_css) : wp_get_theme()->get('Version')
  );
});

add_action('acf/init', function () {
  acf_update_setting('enable_shortcode', true);
});

add_filter('blocksy:general:shortcodes:blocksy-posts:args', function ($args, $shortcode_args) {
  if (empty($shortcode_args['shortcode_instance']) || $shortcode_args['shortcode_instance'] !== 'events-for-current-workshop') {
    return $args;
  }

  if (!is_singular('workshop')) {
    return $args;
  }

  $parent_id = get_queried_object_id();
  if (!$parent_id) {
    return $args;
  }

  // Aggiungi filtro meta per mostrare solo eventi legati al workshop corrente
  $args['meta_query'] = [
    [
      'key' => 'related_workshop',
      'value' => $parent_id,
      'compare' => '=',
    ]
  ];

  // Opzionale: ordina per data evento
  $args['meta_key'] = 'event_date';
  $args['orderby'] = 'meta_value';
  $args['order'] = 'ASC';

  return $args;
}, 10, 2);

function my_acf_google_map_api($api)
{
  $api['key'] = 'AIzaSyDLYoNfsD5UkQkUOK7nSkzAXpv7-_83URI';
  return $api;
}
add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');


function acf_google_map_shortcode($atts)
{
  $location = get_field('location');
  if (!$location || !isset($location['lat'], $location['lng']))
    return '';

  ob_start(); ?>
  <div style="position:relative;width:100%;padding-bottom:56.25%;"> <!-- 16:9 fisso -->
    <iframe
      src="https://www.google.com/maps/embed/v1/place?key=AIzaSyDLYoNfsD5UkQkUOK7nSkzAXpv7-_83URI&q=<?php echo $location['lat']; ?>,<?php echo $location['lng']; ?>"
      style="position:absolute;top:0;left:0;width:100%;height:70%;border:0;" loading="lazy" allowfullscreen
      referrerpolicy="no-referrer-when-downgrade"></iframe>
  </div>
  <?php
  return ob_get_clean();
}
add_shortcode('acf_google_map', 'acf_google_map_shortcode');


// [acf_gmap_line field="location" which="line1|line2|full"]
add_shortcode('acf_gmap_line', function ($atts) {
  $a = shortcode_atts(['field' => '', 'which' => 'full', 'post_id' => get_the_ID()], $atts);
  if (!$a['field'])
    return '';
  $d = get_field($a['field'], $a['post_id']);
  if (!is_array($d))
    return '';

  $line1 = trim(($d['street_number'] ?? '') . ' ' . ($d['street_name'] ?? ($d['street'] ?? '')));
  $line2 = trim(($d['post_code'] ?? '') . ' ' . ($d['city'] ?? ''));
  $country = $d['country'] ?? '';
  $full = trim(($d['address'] ?? '') ?: trim($line1 . ' ' . ($line2 ? ', ' . $line2 : '') . ($country ? ', ' . $country : '')));

  $map = [
    'line1' => $line1,
    'line2' => $line2 ?: ($d['city'] ?? ''),
    'line3' => $country,
    'full' => $full,
  ];
  return esc_html($map[$a['which']] ?? ($d[$a['which']] ?? ''));
});


add_shortcode('acf_datetime', function ($atts) {
  $a = shortcode_atts([
    'field' => '',           // nome campo
    'format' => 'j F Y, H:i', // formato
    'post_id' => get_the_ID(),
    'value' => '',         // opzionale: valore diretto),
  ], $atts, 'acf_datetime');

  if (!$a['field'])
    return '';

  $raw = get_field($a['field'], $a['post_id']);
  if (!$raw)
    $raw = get_post_meta($a['post_id'], $a['field'], true);
  if (!$raw)
    return get_field('event_datssse');

  $ts = strtotime($raw);
  if (!$ts)
    return esc_html($raw);

  return esc_html(date_i18n($a['format'], $ts));
});

add_shortcode('ted_gallery', function ($atts) {
  $a = shortcode_atts([
    'field' => '',
    'columns' => '3',
    'post_id' => get_the_ID(),
  ], $atts, 'ted_gallery');

  if (!$a['field'])
    return '';

  $image_ids = get_field($a['field'], $a['post_id']);
  if ($image_ids) {

    // Generate string of ids ("123,456,789").
    $images_string = implode(',', $image_ids);

    // Generate and do shortcode.
    // Note: The following string is split to simply prevent our own website from rendering the gallery shortcode.
    $shortcode = sprintf('[show_gallery ids="%s" cols="3" size="large"]', esc_attr($images_string), esc_attr($a['columns']));
    return do_shortcode($shortcode);
  }
});

add_shortcode('show_gallery', function ($atts) {
    $a = shortcode_atts([
        'ids'   => '',
        'cols'  => 3,
        'size'  => 'large',
        'ratio' => '4:3',
        'class' => '',
    ], $atts, 'show_gallery');

    if (!$a['ids']) {
        return '';
    }

    $ids = array_filter(array_map('intval', explode(',', $a['ids'])));
    if (empty($ids)) {
        return '';
    }

    // Parse ratio "4:3" -> percentuale per il padding-bottom
    $ratio_w = 4;
    $ratio_h = 3;
    if (strpos($a['ratio'], ':') !== false) {
        [$rw, $rh] = array_map('trim', explode(':', $a['ratio'], 2));
        if ($rw > 0 && $rh > 0) {
            $ratio_w = (float) $rw;
            $ratio_h = (float) $rh;
        }
    }
    $padding = 100 * ($ratio_h / $ratio_w); // es. 3/4 = 75%

    $cols = max(1, (int) $a['cols']);

    ob_start(); ?>
    <div class="show-gallery <?php echo esc_attr($a['class']); ?>"
         data-cols="<?php echo esc_attr($cols); ?>"
         data-padding="<?php echo esc_attr($padding); ?>">
        <?php foreach ($ids as $id):
            $img = wp_get_attachment_image_src($id, $a['size']);
            if (!$img) continue;
            $alt = get_post_meta($id, '_wp_attachment_image_alt', true); ?>
            <figure class="show-gallery__item">
                <img src="<?php echo esc_url($img[0]); ?>"
                     alt="<?php echo esc_attr($alt); ?>"
                     class="show-gallery__img">
            </figure>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
});




add_action('wpforms_process_complete', function ($fields, $entry, $form_data, $entry_id) {
  if (isset($fields['1'])) {
    $email = sanitize_email($fields['1']['value']);
    error_log('Email: ' . $email);
  }
}, 10, 4);




add_filter('render_block', function ($block_content, $block) {

    if (
        empty($block['attrs']['className']) ||
        ! str_contains($block['attrs']['className'], 'has-shortcode')
    ) {
        return $block_content;
    }

    // Questo è IL BLOCCO GIUSTO, non il parent, non i fratelli
    return do_shortcode($block_content);

}, 10, 2);


add_filter(
    'blocksy:general:blocks:query:args',
    function ($query_args, $attributes) {

        // 1) Colpisci solo QUEL blocco
        if (
            empty($attributes['uniqueId']) ||
            $attributes['uniqueId'] !== '6f52b29b'
        ) {
            return $query_args;
        }

        // 2) Solo dentro il single "show"
        if (!is_singular('show')) {
            return $query_args;
        }

        $show_id = get_queried_object_id();
        if (!$show_id) {
            return $query_args;
        }

        // 3) Qui forziamo la query: performances collegate a questo show
      //  $query_args['post_type']      = 'performance';
        $query_args['posts_per_page'] = -1;

        // Se il campo ACF salva un singolo ID
        $query_args['meta_query'] = [
            [
                'key'     => 'related_show', // <-- NOME META (spesso = nome field ACF)
                'value'   => (string) $show_id,
                'compare' => '='
            ]
        ];

        // opzionale: ordinamento
        $query_args['orderby'] = 'date';
        $query_args['order']   = 'ASC';

        return $query_args;
    },
    10,
    2
);