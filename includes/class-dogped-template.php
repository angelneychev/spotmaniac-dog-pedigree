<?php

/**
 * Template loader (theme override system) and shortcode registration.
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dogped_Template
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_filter('template_include', array( $this, 'template_include' ));
        add_action('wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ));
        $this->register_shortcodes();
    }

    public function enqueue_frontend_assets()
    {
        if (is_singular(DOGPED_POST_TYPE) || is_post_type_archive(DOGPED_POST_TYPE) || $this->has_dogped_shortcode()) {
            wp_enqueue_style(
                'dogped-frontend',
                DOGPED_URL . 'assets/css/dogped-frontend.css',
                array(),
                DOGPED_VERSION
            );
            wp_enqueue_script(
                'dogped-frontend',
                DOGPED_URL . 'assets/js/dogped-frontend.js',
                array(),
                DOGPED_VERSION,
                true
            );
        }
    }

    private function has_dogped_shortcode()
    {
        global $post;
        if (! $post) {
            return false;
        }

        $shortcodes = apply_filters('dogped_known_shortcodes', array( 'dogped-catalog', 'dogped-featured' ));
        foreach ($shortcodes as $tag) {
            if (has_shortcode($post->post_content, $tag)) {
                return true;
            }
        }
        return false;
    }

    private function register_shortcodes()
    {
        add_shortcode('dogped-catalog', array( $this, 'shortcode_catalog' ));
        add_shortcode('dogped-featured', array( $this, 'shortcode_featured' ));
    }

    public function shortcode_catalog($atts)
    {
        $atts = shortcode_atts(array(
            'count'        => (int) dogped_get_option('catalog_count', 12),
            'sex'          => '',
            'orderby'      => dogped_get_option('catalog_orderby', 'title'),
            'show_filters' => 'yes',
        ), $atts, 'dogped-catalog');

        $query_args = array(
            'post_type'      => DOGPED_POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => (int) $atts['count'],
            'paged'          => max(1, get_query_var('paged', 1)),
            'orderby'        => $atts['orderby'],
            'order'          => 'ASC',
        );

        $meta_query = array();
        if (! empty($atts['sex'])) {
            $meta_query[] = array( 'key' => 'dogped_sex', 'value' => sanitize_text_field($atts['sex']) );
        }
        $params = Dogped_Validator::validate_search($_GET); // phpcs:ignore WordPress.Security.NonceVerification
        if (! empty($params['sex'])) {
            $meta_query[] = array( 'key' => 'dogped_sex', 'value' => $params['sex'] );
        }
        if (! empty($params['color'])) {
            $meta_query[] = array( 'key' => 'dogped_color', 'value' => $params['color'] );
        }
        if (! empty($params['s'])) {
            $query_args['s'] = $params['s'];
        }
        if (! empty($meta_query)) {
            $query_args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery
        }

        $dogped_query = new WP_Query($query_args);

        ob_start();

        if ('yes' === $atts['show_filters']) {
            ?>
            <form class="dogped-filters" method="get">
                <div class="dogped-filters__row">
                    <input type="text" name="s" value="<?php echo esc_attr($params['s'] ?? ''); ?>"
                           placeholder="<?php esc_attr_e('Search dogs...', 'spotmaniac-dog-pedigree'); ?>" class="dogped-filters__search" />
                    <select name="sex" class="dogped-filters__select">
                        <option value=""><?php esc_html_e('All sexes', 'spotmaniac-dog-pedigree'); ?></option>
                        <option value="male" <?php selected($params['sex'] ?? '', 'male'); ?>><?php esc_html_e('Male', 'spotmaniac-dog-pedigree'); ?></option>
                        <option value="female" <?php selected($params['sex'] ?? '', 'female'); ?>><?php esc_html_e('Female', 'spotmaniac-dog-pedigree'); ?></option>
                    </select>
                    <button type="submit" class="dogped-filters__submit"><?php esc_html_e('Filter', 'spotmaniac-dog-pedigree'); ?></button>
                </div>
            </form>
            <?php
        }

        if ($dogped_query->have_posts()) {
            echo '<div class="dogped-grid">';
            while ($dogped_query->have_posts()) {
                $dogped_query->the_post();
                dogped_get_template('content-dog-card.php', array( 'dog_id' => get_the_ID() ));
            }
            echo '</div>';

            $big = 999999999;
            echo '<nav class="dogped-pagination">';
            echo wp_kses_post(paginate_links(array(
                'base'    => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format'  => '?paged=%#%',
                'current' => max(1, get_query_var('paged', 1)),
                'total'   => $dogped_query->max_num_pages,
            )));
            echo '</nav>';

            wp_reset_postdata();
        } else {
            echo '<p class="dogped-no-results">' . esc_html__('No dogs found.', 'spotmaniac-dog-pedigree') . '</p>';
        }

        return ob_get_clean();
    }

    public function shortcode_featured($atts)
    {
        $atts = shortcode_atts(array(
            'count' => 6,
            'sex'   => '',
        ), $atts, 'dogped-featured');

        $query_args = array(
            'post_type'      => DOGPED_POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => (int) $atts['count'],
            'orderby'        => 'rand',
        );

        if (! empty($atts['sex'])) {
            $query_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
                array( 'key' => 'dogped_sex', 'value' => sanitize_text_field($atts['sex']) ),
            );
        }

        $dogped_query = new WP_Query($query_args);

        ob_start();
        if ($dogped_query->have_posts()) {
            echo '<div class="dogped-grid">';
            while ($dogped_query->have_posts()) {
                $dogped_query->the_post();
                dogped_get_template('content-dog-card.php', array( 'dog_id' => get_the_ID() ));
            }
            echo '</div>';
            wp_reset_postdata();
        }
        return ob_get_clean();
    }

    public function template_include($template)
    {
        if (is_post_type_archive(DOGPED_POST_TYPE) || $this->is_dog_search()) {
            $custom = $this->locate_template('archive-dog.php');
            if ($custom) {
                return $custom;
            }
        }

        if (is_singular(DOGPED_POST_TYPE)) {
            $custom = $this->locate_template('single-dog.php');
            if ($custom) {
                return $custom;
            }
        }

        return $template;
    }

    private function is_dog_search()
    {
        return is_post_type_archive(DOGPED_POST_TYPE) && ! empty(get_query_var('s'));
    }

    public function locate_template($template_name)
    {
        $theme_template = locate_template('spotmaniac-dog-pedigree/' . $template_name);
        if ($theme_template) {
            return $theme_template;
        }

        $plugin_template = DOGPED_PATH . 'templates/' . $template_name;
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }

        return false;
    }
}

/**
 * Load a template partial. Checks theme/spotmaniac-dog-pedigree/{name} first, then plugin templates/.
 *
 * @param string $template_name Template file name.
 * @param array  $args          Variables to pass to the template.
 */
function dogped_get_template($template_name, $args = array())
{
    $theme_template = locate_template('spotmaniac-dog-pedigree/' . $template_name);
    if ($theme_template) {
        load_template($theme_template, false, $args);
    } else {
        $plugin_template = DOGPED_PATH . 'templates/' . $template_name;
        if (file_exists($plugin_template)) {
            load_template($plugin_template, false, $args);
        }
    }
}
