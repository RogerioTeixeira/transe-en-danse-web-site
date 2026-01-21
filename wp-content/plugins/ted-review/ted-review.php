<?php
/**
 * Plugin Name: TED Review Widget
 * Description: [ted_review src="URL" limit="12" min_rating="0" per_view="3" autoplay="5000" class="..."]
 * Version: 0.1.1
 */
if (!defined('ABSPATH')) exit;

define('TED_REVIEW_DIR', plugin_dir_path(__FILE__));
define('TED_REVIEW_URL', plugin_dir_url(__FILE__));

function ted_review_enqueue_assets() {
  $asset_file = TED_REVIEW_DIR . 'build/index.asset.php';
  $deps = ['wp-element']; $ver = null;
  if (file_exists($asset_file)) {
    $asset = include $asset_file; // ['dependencies'=>..., 'version'=>...]
    $deps = $asset['dependencies'] ?? $deps;
    $ver  = $asset['version'] ?? null;
  }
  wp_enqueue_script('ted-review', TED_REVIEW_URL.'build/index.js', $deps, $ver, true);
  if (file_exists(TED_REVIEW_DIR.'build/style-index.css')) {
    wp_enqueue_style('ted-review', TED_REVIEW_URL.'build/style-index.css', [], $ver);
  }
  if (file_exists(TED_REVIEW_DIR.'build/index.css')) {
    wp_enqueue_style('index-glide', TED_REVIEW_URL.'build/index.css', [], $ver);
  }
}

/**
 * SHORTCODE: [ted_review src="..." limit="12" min_rating="0" per_view="3" autoplay="5000" class="my-class"]
 * Non devi creare nessun <div>: lo fa lui e passa le props in data-props JSON.
 */
function ted_review_shortcode($atts) {
  $atts = shortcode_atts([
    'src'        => '',
    'limit'      => '12',
    'min_rating' => '0',
    'per_view'   => '3',
    'autoplay'   => '5000',
    'class'      => '',
  ], $atts, 'ted_review');

  if (empty($atts['src'])) return '<!-- ted_review: missing src -->';

  ted_review_enqueue_assets();

  $props = [
    'src'       => esc_url_raw($atts['src']),
    'limit'     => (int) $atts['limit'],
    'minRating' => (float) $atts['min_rating'],
    'perView'   => (int) $atts['per_view'],
    'autoplay'  => (int) $atts['autoplay'],
  ];

  $id   = 'ted-review-'.wp_generate_uuid4();
  $json = esc_attr( wp_json_encode($props, JSON_UNESCAPED_SLASHES) );
  $cls  = trim('ted-review-mount '.sanitize_html_class($atts['class']));

  // Questo È il container creato AUTOMATICAMENTE dallo shortcode.
  return sprintf(
    '<div id="%s" class="%s" data-props="%s"></div>',
    esc_attr($id),
    esc_attr($cls),
    $json
  );
}
add_shortcode('ted_review', 'ted_review_shortcode');