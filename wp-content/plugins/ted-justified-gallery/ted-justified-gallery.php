<?php
/**
 * Plugin Name: TED Justified Gallery
 * Description: Justified gallery (hero + last row full) using fjGallery + GLightbox.
 * Author: Rogerio
 * Version: 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue assets
 */
function ted_justified_gallery_enqueue() {

    // fjGallery (Flickr Justified Gallery) - layout justified moderno
    wp_enqueue_style(
        'fjgallery',
        'https://cdn.jsdelivr.net/npm/flickr-justified-gallery@2.1/dist/fjGallery.css',
        [],
        '2.1.0'
    );

    wp_enqueue_script(
        'fjgallery',
        'https://cdn.jsdelivr.net/npm/flickr-justified-gallery@2.1/dist/fjGallery.min.js',
        [],
        '2.1.0',
        true
    );

    // GLightbox per il lightbox
    wp_enqueue_style(
        'glightbox',
        'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css',
        [],
        '3.3.0'
    );

    wp_enqueue_script(
        'glightbox',
        'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js',
        [ 'fjgallery' ],
        '3.3.0',
        true
    );

    // I tuoi asset
    wp_enqueue_style(
        'ted-justified-gallery',
        plugin_dir_url( __FILE__ ) . 'assets/ted-justified-gallery.css',
        [ 'fjgallery' ],
        '2.0.0'
    );

    wp_enqueue_script(
        'ted-justified-gallery',
        plugin_dir_url( __FILE__ ) . 'assets/ted-justified-gallery.js',
        [ 'fjgallery', 'glightbox' ],
        '2.0.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'ted_justified_gallery_enqueue' );

/**
 * Shortcode: [ted_justified_gallery ids="1,2,3" size="large"]
 */
function ted_justified_gallery_shortcode( $atts ) {

    $atts = shortcode_atts(
        [
            'ids'  => '',
            'size' => 'large',
        ],
        $atts,
        'ted_justified_gallery'
    );

    $ids = array_filter( array_map( 'absint', explode( ',', $atts['ids'] ) ) );
    if ( empty( $ids ) ) {
         $acf_gallery = get_field( 'gallery' );
         if ( empty( $acf_gallery ) ) {
             return '';
         }
    
         if ( is_int( $acf_gallery[0] ) ) {
             $ids = $acf_gallery;
         } elseif ( isset( $acf_gallery[0]['ID'] ) ) {
             $ids = array_column( $acf_gallery, 'ID' );
         }
     }
    if ( empty( $ids ) ) {
        return '';
    }

    // Prima immagine = HERO
    $hero_id = array_shift( $ids );

    ob_start();

    

    // GALLERIA JUSTIFIED sotto l'hero
    if ( ! empty( $ids ) ) : ?>
        <div class="ted-justified-gallery fj-gallery" data-ted-justified-gallery>
            <?php foreach ( $ids as $id ) :

                $thumb = wp_get_attachment_image_src( $id, $atts['size'] );
                $full  = wp_get_attachment_image_src( $id, 'full' );

                if ( ! $thumb ) {
                    continue;
                }

                $thumb_url = $thumb[0];
                $thumb_w   = (int) $thumb[1];
                $thumb_h   = (int) $thumb[2];

                $full_url  = $full ? $full[0] : $thumb_url;
                $full_w    = $full ? (int) $full[1] : $thumb_w;
                $full_h    = $full ? (int) $full[2] : $thumb_h;

                $alt       = get_post_meta( $id, '_wp_attachment_image_alt', true );
                ?>
                <a
                    href="<?php echo esc_url( $full_url ); ?>"
                    class="tjg-item fj-gallery-item tjg-link"
                    data-pswp-width="<?php echo esc_attr( $full_w ); ?>"
                    data-pswp-height="<?php echo esc_attr( $full_h ); ?>"
                >
                    <img
                        src="<?php echo esc_url( $thumb_url ); ?>"
                        alt="<?php echo esc_attr( $alt ); ?>"
                        loading="lazy"
                        width="<?php echo esc_attr( $thumb_w ); ?>"
                        height="<?php echo esc_attr( $thumb_h ); ?>"
                    >
                </a>
            <?php endforeach; ?>
        </div>
    <?php
    endif;

    // HERO
    if ( $hero_id ) {

        $hero_img = wp_get_attachment_image_src( $hero_id, 'full' );
        if ( $hero_img ) {
            $hero_url = $hero_img[0];
            $hero_alt = get_post_meta( $hero_id, '_wp_attachment_image_alt', true );
            ?>
            <div class="tjg-hero">
                <a href="<?php echo esc_url( $hero_url ); ?>" class="tjg-hero-link tjg-link">
                    <img
                        src="<?php echo esc_url( $hero_url ); ?>"
                        alt="<?php echo esc_attr( $hero_alt ); ?>"
                        loading="lazy"
                    >
                </a>
            </div>
            <?php
        }
    }

    return ob_get_clean();
}
add_shortcode( 'ted_justified_gallery', 'ted_justified_gallery_shortcode' );