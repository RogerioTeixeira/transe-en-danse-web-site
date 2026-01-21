<?php
/**
 * ACF Block render template
 * Repeater: items
 * Sub field (text): text
 */

$repeater_name = get_field('source_repeater'); 
if (!$repeater_name) return;
if (have_rows($repeater_name, $post_id)) {
    echo '<ul class="ted-acf-repeater-list">';
    while (have_rows($repeater_name, $post_id)) {
        the_row();
        echo '<li>' . esc_html(get_sub_field('value')) . '</li>';
    }
    echo '</ul>';
}