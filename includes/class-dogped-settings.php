<?php

/**
 * Settings page using the WordPress Settings API + repeater UI.
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dogped_Settings
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
        add_action('admin_menu', array( $this, 'add_settings_page' ));
        add_action('admin_init', array( $this, 'register_settings' ));
    }

    public function add_settings_page()
    {
        add_submenu_page(
            'edit.php?post_type=' . DOGPED_POST_TYPE,
            __('Spotmaniac Dog Pedigree Settings', 'spotmaniac-dog-pedigree'),
            __('Settings', 'spotmaniac-dog-pedigree'),
            'manage_options',
            'dogped-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings()
    {
        // --- Display visibility toggles ---
        add_settings_section(
            'dogped_display_section',
            __('Display Settings', 'spotmaniac-dog-pedigree'),
            array( $this, 'display_section_cb' ),
            'dogped-settings'
        );

        $visibility_fields = array(
            'dogped_show_registration_number' => __('Registration Number', 'spotmaniac-dog-pedigree'),
            'dogped_show_registration_date'   => __('Registration Date', 'spotmaniac-dog-pedigree'),
            'dogped_show_tattoo'              => __('Tattoo Number', 'spotmaniac-dog-pedigree'),
            'dogped_show_microchip'           => __('Microchip', 'spotmaniac-dog-pedigree'),
            'dogped_show_club_number'         => __('Club Number', 'spotmaniac-dog-pedigree'),
            'dogped_show_breeder'             => __('Breeder', 'spotmaniac-dog-pedigree'),
            'dogped_show_health'              => __('Health', 'spotmaniac-dog-pedigree'),
            'dogped_show_birth_date'          => __('Birth Date', 'spotmaniac-dog-pedigree'),
            'dogped_show_death_date'          => __('Death Date', 'spotmaniac-dog-pedigree'),
            'dogped_show_titles'              => __('Titles', 'spotmaniac-dog-pedigree'),
            'dogped_show_description'         => __('Description', 'spotmaniac-dog-pedigree'),
        );
        foreach ($visibility_fields as $key => $label) {
            register_setting('dogped_settings_group', $key, array( 'sanitize_callback' => array( $this, 'sanitize_checkbox' ), 'default' => 'yes' ));
            add_settings_field($key, $label, array( $this, 'render_checkbox' ), 'dogped-settings', 'dogped_display_section', array( 'label_for' => $key ));
        }
        register_setting('dogped_settings_group', 'dogped_section_order', array( 'sanitize_callback' => 'sanitize_text_field' ));

        // --- URL ---
        add_settings_section('dogped_url_section', __('URL & Pages', 'spotmaniac-dog-pedigree'), '__return_null', 'dogped-settings');
        register_setting('dogped_settings_group', 'dogped_url_prefix', array( 'sanitize_callback' => 'sanitize_title', 'default' => 'dogs' ));
        add_settings_field('dogped_url_prefix', __('URL Prefix', 'spotmaniac-dog-pedigree'), array( $this, 'render_text' ), 'dogped-settings', 'dogped_url_section', array(
            'label_for'   => 'dogped_url_prefix',
            'description' => __('Slug for dog pages. After changing, go to Settings → Permalinks and click Save.', 'spotmaniac-dog-pedigree'),
        ));

        // --- Catalog ---
        add_settings_section('dogped_catalog_section', __('Catalog', 'spotmaniac-dog-pedigree'), '__return_null', 'dogped-settings');
        register_setting('dogped_settings_group', 'dogped_catalog_count', array( 'sanitize_callback' => 'absint', 'default' => 12 ));
        add_settings_field('dogped_catalog_count', __('Dogs Per Page', 'spotmaniac-dog-pedigree'), array( $this, 'render_number' ), 'dogped-settings', 'dogped_catalog_section', array( 'label_for' => 'dogped_catalog_count', 'min' => 1, 'max' => 100 ));
        register_setting('dogped_settings_group', 'dogped_catalog_orderby', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => 'title' ));
        add_settings_field('dogped_catalog_orderby', __('Default Order', 'spotmaniac-dog-pedigree'), array( $this, 'render_select' ), 'dogped-settings', 'dogped_catalog_section', array(
            'label_for' => 'dogped_catalog_orderby',
            'options'   => array(
                'title'    => __('Name', 'spotmaniac-dog-pedigree'),
                'date'     => __('Date Added', 'spotmaniac-dog-pedigree'),
                'modified' => __('Last Modified', 'spotmaniac-dog-pedigree'),
            ),
        ));

        // --- Photo ---
        add_settings_section('dogped_photo_section', __('Photo', 'spotmaniac-dog-pedigree'), '__return_null', 'dogped-settings');
        register_setting('dogped_settings_group', 'dogped_photo_max_size', array( 'sanitize_callback' => 'intval', 'default' => 5 ));
        add_settings_field('dogped_photo_max_size', __('Max Photo Size (MB)', 'spotmaniac-dog-pedigree'), array( $this, 'render_number' ), 'dogped-settings', 'dogped_photo_section', array(
            'label_for'   => 'dogped_photo_max_size',
            'min'         => -1,
            'max'         => 50,
            'description' => __('-1 for unlimited, 0 to disable uploads.', 'spotmaniac-dog-pedigree'),
        ));

        // --- Dropdown Field Options ---
        add_settings_section('dogped_options_section', __('Dropdown Field Options', 'spotmaniac-dog-pedigree'), array( $this, 'options_section_cb' ), 'dogped-settings');

        $builtin = array(
            'dogped_color_options' => array(
                'label'       => __('Colors', 'spotmaniac-dog-pedigree'),
                'placeholder' => __('e.g. Black', 'spotmaniac-dog-pedigree'),
                'add_label'   => __('+ Add Color', 'spotmaniac-dog-pedigree'),
                'examples'    => 'Black, Brown, White, Red, Cream, Brindle',
            ),
            'dogped_size_options' => array(
                'label'       => __('Sizes', 'spotmaniac-dog-pedigree'),
                'placeholder' => __('e.g. Standard', 'spotmaniac-dog-pedigree'),
                'add_label'   => __('+ Add Size', 'spotmaniac-dog-pedigree'),
                'examples'    => 'Miniature, Standard, Giant',
            ),
            'dogped_breeding_status_options' => array(
                'label'       => __('Breeding Statuses', 'spotmaniac-dog-pedigree'),
                'placeholder' => __('e.g. Approved for breeding', 'spotmaniac-dog-pedigree'),
                'add_label'   => __('+ Add Status', 'spotmaniac-dog-pedigree'),
                'examples'    => 'Approved, Not approved, Retired, Neutered',
            ),
        );

        foreach ($builtin as $key => $field) {
            register_setting('dogped_settings_group', $key, array( 'sanitize_callback' => array( $this, 'sanitize_repeater_json' ) ));
            add_settings_field($key, $field['label'], array( $this, 'render_repeater' ), 'dogped-settings', 'dogped_options_section', array(
                'label_for'   => $key,
                'placeholder' => $field['placeholder'],
                'add_label'   => $field['add_label'],
                'examples'    => $field['examples'],
            ));
        }

        $custom_fields = self::get_custom_dropdown_fields();
        foreach ($custom_fields as $field) {
            $option_key = 'dogped_custom_' . sanitize_key($field['slug']) . '_options';
            register_setting('dogped_settings_group', $option_key, array( 'sanitize_callback' => array( $this, 'sanitize_repeater_json' ) ));
            add_settings_field($option_key, esc_html($field['label']), array( $this, 'render_repeater' ), 'dogped-settings', 'dogped_options_section', array(
                'label_for'   => $option_key,
                'placeholder' => sprintf(/* translators: %s: field name */ __('e.g. a %s value', 'spotmaniac-dog-pedigree'), $field['label']),
                'add_label'   => sprintf(/* translators: %s: field name */ __('+ Add %s', 'spotmaniac-dog-pedigree'), $field['label']),
            ));
        }

        register_setting('dogped_settings_group', 'dogped_custom_dropdown_fields', array( 'sanitize_callback' => array( $this, 'sanitize_custom_fields_json' ) ));

        /**
         * Allows Pro add-ons to register their own settings sections/fields.
         * Receives the page slug ('dogped-settings') and group ('dogped_settings_group').
         */
        do_action('dogped_register_settings', 'dogped-settings', 'dogped_settings_group');
    }

    // =========================================================================
    // Section callbacks
    // =========================================================================

    public function display_section_cb()
    {
        echo '<p>' . esc_html__('Toggle which fields appear on the single dog page.', 'spotmaniac-dog-pedigree') . '</p>';

        $order = json_decode(get_option('dogped_section_order', '[]'), true);
        if (! is_array($order) || empty($order)) {
            $order = array( 'photo', 'basic_info', 'identification', 'health', 'siblings' );
        }
        $labels = array(
            'photo'          => __('Photo', 'spotmaniac-dog-pedigree'),
            'basic_info'     => __('Basic Info', 'spotmaniac-dog-pedigree'),
            'identification' => __('Identification Numbers', 'spotmaniac-dog-pedigree'),
            'health'         => __('Health', 'spotmaniac-dog-pedigree'),
            'siblings'       => __('Litter Mates / Siblings', 'spotmaniac-dog-pedigree'),
        );

        /**
         * Allow Pro add-ons to register additional sections (e.g. pedigree).
         */
        $labels = apply_filters('dogped_section_labels', $labels);

        echo '<h4>' . esc_html__('Drag to reorder sections:', 'spotmaniac-dog-pedigree') . '</h4>';
        echo '<ul id="dogped-sortable-sections" class="dogped-sortable">';
        foreach ($order as $key) {
            if (isset($labels[ $key ])) {
                echo '<li data-section="' . esc_attr($key) . '"><span class="dashicons dashicons-menu"></span> ' . esc_html($labels[ $key ]) . '</li>';
            }
        }
        echo '</ul>';
        echo '<input type="hidden" name="dogped_section_order" id="dogped_section_order" value="' . esc_attr(wp_json_encode($order)) . '" />';
    }

    public function options_section_cb()
    {
        echo '<p>' . esc_html__('Configure the dropdown values for each field. Use the buttons to add/remove options.', 'spotmaniac-dog-pedigree') . '</p>';

        $custom_fields = self::get_custom_dropdown_fields();
        ?>
        <div id="dogped-custom-fields-manager" style="margin-top:10px; padding:12px; background:#f9f9f9; border:1px solid #ddd; border-radius:4px;">
            <h4 style="margin-top:0;"><?php esc_html_e('Custom Dropdown Fields', 'spotmaniac-dog-pedigree'); ?></h4>
            <p class="description"><?php esc_html_e('Add custom dropdown fields beyond Colors, Sizes, and Breeding Status. After adding, save the page - options inputs will appear below.', 'spotmaniac-dog-pedigree'); ?></p>

            <?php if (! empty($custom_fields)) : ?>
                <ul id="dogped-custom-fields-list" style="margin:8px 0;">
                    <?php foreach ($custom_fields as $i => $field) : ?>
                        <li class="dogped-repeater-item" data-index="<?php echo esc_attr($i); ?>" style="display:flex;align-items:center;gap:8px;margin:4px 0;">
                            <span class="dashicons dashicons-menu" style="color:#999;cursor:move;"></span>
                            <strong><?php echo esc_html($field['label']); ?></strong>
                            <code style="font-size:11px;">dogped_custom_<?php echo esc_html($field['slug']); ?></code>
                            <button type="button" class="button-link dogped-remove-custom-field" data-index="<?php echo esc_attr($i); ?>" style="color:#b32d2e;"><?php esc_html_e('Remove', 'spotmaniac-dog-pedigree'); ?></button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div style="display:flex;gap:8px;align-items:center;margin-top:8px;">
                <input type="text" id="dogped-new-field-label" placeholder="<?php esc_attr_e('Field name, e.g. Coat Type', 'spotmaniac-dog-pedigree'); ?>" class="regular-text" />
                <button type="button" class="button" id="dogped-add-custom-field"><?php esc_html_e('+ Add Field', 'spotmaniac-dog-pedigree'); ?></button>
            </div>

            <input type="hidden" name="dogped_custom_dropdown_fields" id="dogped_custom_dropdown_fields"
                   value="<?php echo esc_attr(wp_json_encode($custom_fields)); ?>" />
        </div>
        <?php
    }

    // =========================================================================
    // Field renderers
    // =========================================================================

    public function render_repeater($args)
    {
        $key         = $args['label_for'];
        $placeholder = $args['placeholder'] ?? '';
        $add_label   = $args['add_label'] ?? __('+ Add', 'spotmaniac-dog-pedigree');
        $examples    = $args['examples'] ?? '';

        $raw   = get_option($key, '[]');
        $items = self::decode_repeater($raw);
        ?>
        <div class="dogped-repeater" data-key="<?php echo esc_attr($key); ?>">
            <ul class="dogped-repeater-list" data-placeholder="<?php echo esc_attr($placeholder); ?>">
                <?php foreach ($items as $item) : ?>
                    <li class="dogped-repeater-item">
                        <span class="dashicons dashicons-menu dogped-repeater-handle"></span>
                        <input type="text" name="<?php echo esc_attr($key); ?>_items[]"
                               value="<?php echo esc_attr($item); ?>"
                               placeholder="<?php echo esc_attr($placeholder); ?>"
                               class="regular-text" />
                        <button type="button" class="button dogped-repeater-remove" title="<?php esc_attr_e('Remove', 'spotmaniac-dog-pedigree'); ?>">&times;</button>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="button dogped-repeater-add"><?php echo esc_html($add_label); ?></button>
            <?php if ($examples) : ?>
                <p class="description">
                    <?php
                    printf(
                        /* translators: %s: example values */
                        esc_html__('Examples: %s', 'spotmaniac-dog-pedigree'),
                        esc_html($examples)
                    );
                    ?>
                </p>
            <?php endif; ?>
            <input type="hidden" name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>"
                   value="<?php echo esc_attr(wp_json_encode($items)); ?>"
                   class="dogped-repeater-value" />
        </div>
        <?php
    }

    public static function decode_repeater($raw)
    {
        if (is_array($raw)) {
            return array_values(array_filter(array_map('trim', $raw)));
        }
        if (is_string($raw) && '' !== $raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map('trim', $decoded)));
            }
            return array_values(array_filter(array_map('trim', explode("\n", $raw))));
        }
        return array();
    }

    public function render_checkbox($args)
    {
        $value = get_option($args['label_for'], 'yes');
        printf('<input type="checkbox" id="%s" name="%s" value="yes" %s />', esc_attr($args['label_for']), esc_attr($args['label_for']), checked($value, 'yes', false));
    }

    public function render_text($args)
    {
        $value = get_option($args['label_for'], '');
        printf('<input type="text" id="%s" name="%s" value="%s" class="regular-text" />', esc_attr($args['label_for']), esc_attr($args['label_for']), esc_attr($value));
        if (! empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }

    public function render_number($args)
    {
        $value = get_option($args['label_for'], '');
        printf('<input type="number" id="%s" name="%s" value="%s" min="%s" max="%s" class="small-text" />', esc_attr($args['label_for']), esc_attr($args['label_for']), esc_attr($value), esc_attr($args['min'] ?? 0), esc_attr($args['max'] ?? 999));
        if (! empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }

    public function render_select($args)
    {
        $value = get_option($args['label_for'], '');
        printf('<select id="%s" name="%s">', esc_attr($args['label_for']), esc_attr($args['label_for']));
        foreach (( $args['options'] ?? array() ) as $k => $label) {
            printf('<option value="%s" %s>%s</option>', esc_attr($k), selected($value, $k, false), esc_html($label));
        }
        echo '</select>';
    }

    // =========================================================================
    // Sanitization
    // =========================================================================

    public function sanitize_checkbox($v)
    {
        return 'yes' === $v ? 'yes' : 'no';
    }

    public function sanitize_repeater_json($value)
    {
        if (is_array($value)) {
            $items = array_values(array_filter(array_map('sanitize_text_field', $value)));
            return wp_json_encode($items);
        }
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $items = array_values(array_filter(array_map('sanitize_text_field', $decoded)));
            return wp_json_encode($items);
        }
        if (is_string($value) && '' !== $value) {
            $items = array_values(array_filter(array_map('sanitize_text_field', explode("\n", $value))));
            return wp_json_encode($items);
        }
        return '[]';
    }

    public function sanitize_custom_fields_json($value)
    {
        $fields = json_decode($value, true);
        if (! is_array($fields)) {
            return '[]';
        }
        $clean = array();
        foreach ($fields as $field) {
            if (empty($field['label']) || empty($field['slug'])) {
                continue;
            }
            $clean[] = array(
                'label' => sanitize_text_field($field['label']),
                'slug'  => sanitize_key($field['slug']),
            );
        }
        return wp_json_encode($clean);
    }

    public static function get_custom_dropdown_fields()
    {
        $raw    = get_option('dogped_custom_dropdown_fields', '[]');
        $fields = json_decode($raw, true);
        return is_array($fields) ? $fields : array();
    }

    // =========================================================================
    // Page render
    // =========================================================================

    public function render_settings_page()
    {
        if (! current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <?php settings_errors(); ?>
            <form action="options.php" method="post">
                <?php
                settings_fields('dogped_settings_group');
                do_settings_sections('dogped-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
