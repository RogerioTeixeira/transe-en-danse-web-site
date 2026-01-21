<?php
/**
 * Plugin Name: TED Image Gallery
 * Description: [ted_review src="URL" limit="12" min_rating="0" per_view="3" autoplay="5000" class="..."]
 * Version: 0.1.0
 */
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
    // registra + enqueua il CSS solo se serve
    wp_enqueue_style(
        'ted-image-gallery',
        plugin_dir_url(__FILE__) . 'assets/css/ted-image-gallery.css',
        [],
        '1.0.0'
    );
});

/**
 * SHORTCODE: [ted_image_gallery src="..." limit="12" min_rating="0" per_view="3" autoplay="5000" class="my-class"]
 * Non devi creare nessun <div>: lo fa lui e passa le props in data-props JSON.
 */
add_shortcode('ted_image_gallery', function () {

    ob_start(); ?>

<article class="ted-gallery">
  <a href="https://alvaromontoro.com" target="_top">
    <img src="http://localhost:8080/wp-content/uploads/2025/07/hero-image-1024x685.jpg" alt="guitar player at concert" />
  </a>
  <a href="https://comicss.art" target="_top">
    <img src="http://localhost:8080/wp-content/uploads/2025/08/coline-fly-scaled.jpg" alt="duo singing" />
  </a>
  <a href="https://twitter.com/alvaro_montoro" target="_top">
    <img src="http://localhost:8080/wp-content/uploads/2025/12/decor-Danse-En-Papier-07124.webp" alt="crowd cheering" />
  </a>
  <a href="https://www.linkedin.com/in/alvaromontoro/" target="_top">
    <img src="http://localhost:8080/wp-content/uploads/2025/08/DSC_8992-681x1024.jpg" alt="singer performing" />
  </a>
   <a href="https://www.linkedin.com/in/alvaromontoro/" target="_top">
    <img src="http://localhost:8080/wp-content/uploads/2025/08/DSC_8992-681x1024.jpg" alt="singer performing" />
  </a>
   <a href="https://twitter.com/alvaro_montoro" target="_top">
    <img src="http://localhost:8080/wp-content/uploads/2025/12/decor-Danse-En-Papier-07124.webp" alt="crowd cheering" />
  </a>
  <a href="https://www.linkedin.com/in/alvaromontoro/" target="_top">
    <img src="http://localhost:8080/wp-content/uploads/2025/08/DSC_8992-681x1024.jpg" alt="singer performing" />
  </a>
   <a href="https://twitter.com/alvaro_montoro" target="_top">
    <img src="http://localhost:8080/wp-content/uploads/2025/12/decor-Danse-En-Papier-07124.webp" alt="crowd cheering" />
  </a>
  <a href="https://www.linkedin.com/in/alvaromontoro/" target="_top">
    <img src="http://localhost:8080/wp-content/uploads/2025/08/DSC_8992-681x1024.jpg" alt="singer performing" />
  </a>
   <a href="https://twitter.com/alvaro_montoro" target="_top">
    <img src="http://localhost:8080/wp-content/uploads/2025/12/decor-Danse-En-Papier-07124.webp" alt="crowd cheering" />
  </a>
  <a href="https://www.linkedin.com/in/alvaromontoro/" target="_top">
    <img src="http://localhost:8080/wp-content/uploads/2025/08/DSC_8992-681x1024.jpg" alt="singer performing" />
  </a>
   <a href="https://twitter.com/alvaro_montoro" target="_top">
    <img src="http://localhost:8080/wp-content/uploads/2025/12/decor-Danse-En-Papier-07124.webp" alt="crowd cheering" />
  </a>
  <a href="https://www.linkedin.com/in/alvaromontoro/" target="_top">
    <img src="http://localhost:8080/wp-content/uploads/2025/08/DSC_8992-681x1024.jpg" alt="singer performing" />
  </a>
</article>

    <?php
    return ob_get_clean();
});