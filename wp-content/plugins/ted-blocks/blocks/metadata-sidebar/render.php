<?php
/**
 * ACF Block render template
 * Repeater: meta_data
 * Sub fields:
 *  - label (text)
 *  - value (wysiwyg)
 */

$field_name = 'meta_data';

if (have_rows($field_name, $post_id)) : ?>
    
    <div class="ted-sidebar-container">

        <?php while (have_rows($field_name, $post_id)) :
            the_row();

            $label = get_sub_field('label');
            $value = get_sub_field('value');

            // Skip empty rows
            if (empty($label) && empty($value)) {
                continue;
            }
            ?>

            <div class="ted-sidebar-metadata">

                <?php if (!empty($label)) : ?>
                    <p class="ted-sidebar-metadata-label">
                        <?php echo esc_html($label); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($value)) : ?>
                    <div class="ted-sidebar-metadata-value">
                        <?php echo wp_kses_post($value); ?>
                    </div>
                <?php endif; ?>

            </div>

        <?php endwhile; ?>

    </div>

<?php endif; ?>