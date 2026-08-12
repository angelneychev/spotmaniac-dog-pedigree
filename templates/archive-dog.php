<?php

/**
 * Template: Dog Archive / Catalog page.
 *
 * Override: copy to your-theme/spotmaniac-dog-pedigree/archive-dog.php
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

$dogped_params = Dogped_Validator::validate_search($_GET); // phpcs:ignore WordPress.Security.NonceVerification
?>

<div class="dogped-archive-wrap">

    <h1 class="dogped-archive__title">
        <?php
        if (! empty($dogped_params['s'])) {
            /* translators: %s: search query */
            printf(esc_html__('Search results for: %s', 'spotmaniac-dog-pedigree'), esc_html($dogped_params['s']));
        } else {
            esc_html_e('Dogs', 'spotmaniac-dog-pedigree');
        }
        ?>
    </h1>

    <form class="dogped-filters" method="get" action="<?php echo esc_url(get_post_type_archive_link(DOGPED_POST_TYPE)); ?>">
        <div class="dogped-filters__row">
            <input type="text" name="s" value="<?php echo esc_attr($dogped_params['s'] ?? ''); ?>"
                   placeholder="<?php esc_attr_e('Search dogs...', 'spotmaniac-dog-pedigree'); ?>" class="dogped-filters__search" />

            <select name="sex" class="dogped-filters__select">
                <option value=""><?php esc_html_e('All sexes', 'spotmaniac-dog-pedigree'); ?></option>
                <option value="male" <?php selected($dogped_params['sex'] ?? '', 'male'); ?>><?php esc_html_e('Male', 'spotmaniac-dog-pedigree'); ?></option>
                <option value="female" <?php selected($dogped_params['sex'] ?? '', 'female'); ?>><?php esc_html_e('Female', 'spotmaniac-dog-pedigree'); ?></option>
            </select>

            <?php
            $dogped_colors = Dogped_Utilities::get_field_options('color');
            if (! empty($dogped_colors)) :
                ?>
                <select name="color" class="dogped-filters__select">
                    <option value=""><?php esc_html_e('All colors', 'spotmaniac-dog-pedigree'); ?></option>
                    <?php foreach ($dogped_colors as $dogped_color) : ?>
                        <option value="<?php echo esc_attr($dogped_color); ?>" <?php selected($dogped_params['color'] ?? '', $dogped_color); ?>><?php echo esc_html($dogped_color); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <?php
            $dogped_statuses = Dogped_Utilities::get_field_options('breeding_status');
            if (! empty($dogped_statuses)) :
                ?>
                <select name="breeding_status" class="dogped-filters__select">
                    <option value=""><?php esc_html_e('All statuses', 'spotmaniac-dog-pedigree'); ?></option>
                    <?php foreach ($dogped_statuses as $dogped_status) : ?>
                        <option value="<?php echo esc_attr($dogped_status); ?>" <?php selected($dogped_params['breeding_status'] ?? '', $dogped_status); ?>><?php echo esc_html($dogped_status); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <button type="submit" class="dogped-filters__submit"><?php esc_html_e('Filter', 'spotmaniac-dog-pedigree'); ?></button>
        </div>
    </form>

    <?php
    $dogped_query_args = array(
        'post_type'      => DOGPED_POST_TYPE,
        'post_status'    => 'publish',
        'posts_per_page' => $dogped_params['per_page'],
        'paged'          => $dogped_params['paged'],
        'orderby'        => $dogped_params['orderby'],
        'order'          => $dogped_params['order'],
    );

    if (! empty($dogped_params['s'])) {
        $dogped_query_args['s'] = $dogped_params['s'];
    }

    $dogped_meta_query = array();
    if (! empty($dogped_params['sex'])) {
        $dogped_meta_query[] = array( 'key' => 'dogped_sex', 'value' => $dogped_params['sex'] );
    }
    if (! empty($dogped_params['color'])) {
        $dogped_meta_query[] = array( 'key' => 'dogped_color', 'value' => $dogped_params['color'] );
    }
    if (! empty($dogped_params['breeding_status'])) {
        $dogped_meta_query[] = array( 'key' => 'dogped_breeding_status', 'value' => $dogped_params['breeding_status'] );
    }
    if (! empty($dogped_meta_query)) {
        $dogped_query_args['meta_query'] = $dogped_meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery
    }

    $dogped_query = new WP_Query($dogped_query_args);
    ?>

    <?php if ($dogped_query->have_posts()) : ?>
        <div class="dogped-grid">
            <?php while ($dogped_query->have_posts()) :
                $dogped_query->the_post(); ?>
                <?php dogped_get_template('content-dog-card.php', array( 'dog_id' => get_the_ID() )); ?>
            <?php endwhile; ?>
        </div>

        <?php
        $dogped_big = 999999999;
        echo '<nav class="dogped-pagination">';
        echo wp_kses_post(paginate_links(array(
            'base'    => str_replace($dogped_big, '%#%', esc_url(get_pagenum_link($dogped_big))),
            'format'  => '?paged=%#%',
            'current' => $dogped_params['paged'],
            'total'   => $dogped_query->max_num_pages,
        )));
        echo '</nav>';
        ?>

        <?php wp_reset_postdata(); ?>

    <?php else : ?>
        <p class="dogped-no-results"><?php esc_html_e('No dogs found matching your criteria.', 'spotmaniac-dog-pedigree'); ?></p>
    <?php endif; ?>

</div>

<?php
get_footer();
