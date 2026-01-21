<?php
/**
 * Plugin Name: TED Masonry Gallery
 * Description: Simple Masonry gallery for Transe-en-Danse.
 * Author: Rogerio
 * Version: 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue frontend assets.
 */
function ted_masonry_gallery_enqueue_assets() {
    // Core WP scripts.
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'imagesloaded' );
    wp_enqueue_script( 'masonry' );

    // Styles (layout Masonry).
    wp_enqueue_style(
        'ted-masonry-gallery',
        plugin_dir_url( __FILE__ ) . 'assets/ted-masonry-gallery.css',
        [],
        '1.1.0'
    );

    // GLightbox CSS (CDN).
    wp_enqueue_style(
        'glightbox',
        'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css',
        [],
        '3.3.0'
    );

    // GLightbox JS (CDN).
    wp_enqueue_script(
        'glightbox',
        'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js',
        [],
        '3.3.0',
        true
    );

    // Script principale: Masonry + init GLightbox.
    wp_enqueue_script(
        'ted-masonry-gallery',
        plugin_dir_url( __FILE__ ) . 'assets/ted-masonry-gallery.js',
        [ 'jquery', 'imagesloaded', 'masonry', 'glightbox' ],
        '1.1.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'ted_masonry_gallery_enqueue_assets' );

/**
 * Shortcode: [ted_masonry_gallery ids="1,2,3" size="large"]
 */
function ted_masonry_gallery_shortcode( $atts ) {
    $atts = shortcode_atts(
        [
            'ids'  => '',
            'size' => 'large',
        ],
        $atts,
        'ted_masonry_gallery'
    );

    $ids = array_filter( array_map( 'absint', explode( ',', $atts['ids'] ) ) );
    if ( empty( $ids ) ) {
    $acf_gallery = get_field('gallery');

    // Se ACF non ha nulla → esci
    if ( empty( $acf_gallery ) ) {
        return '';
    }

    // Se ACF restituisce array di ID
    if ( is_int( $acf_gallery[0] ) ) {
        $ids = $acf_gallery;
    }
    // Se ACF restituisce array di immagini
    elseif ( isset( $acf_gallery[0]['ID'] ) ) {
        $ids = array_column( $acf_gallery, 'ID' );
    }
}

    ob_start();
    ?>
    <div class="ted-masonry-gallery" data-ted-masonry>
        <div class="ted-masonry-sizer"></div>

        <?php foreach ( $ids as $id ) :
            $img  = wp_get_attachment_image_src( $id, $atts['size'] );
            if ( ! $img ) {
                continue;
            }

            // versione full per il lightbox
            $full = wp_get_attachment_image_src( $id, 'full' );
            $alt  = get_post_meta( $id, '_wp_attachment_image_alt', true );
            ?>
            <div class="ted-masonry-item">
                <a
                    href="<?php echo esc_url( $full ? $full[0] : $img[0] ); ?>"
                    class="ted-masonry-link"
                    data-gallery="ted-masonry"
                >
                    <img
                        src="<?php echo esc_url( $img[0] ); ?>"
                        alt="<?php echo esc_attr( $alt ); ?>"
                        loading="lazy"
                    >
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode( 'ted_masonry_gallery', 'ted_masonry_gallery_shortcode' );