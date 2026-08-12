<?php

/**
 * Admin meta boxes, columns, and edit screens for the Dog CPT.
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dogped_Admin
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
        add_action('add_meta_boxes', array( $this, 'add_meta_boxes' ));
        add_action('save_post_' . DOGPED_POST_TYPE, array( $this, 'save_meta_boxes' ), 10, 2);
        add_filter('manage_' . DOGPED_POST_TYPE . '_posts_columns', array( $this, 'custom_columns' ));
        add_action('manage_' . DOGPED_POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2);
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ));
        add_action('admin_notices', array( $this, 'show_save_errors' ));
    }

    /**
     * Surfaces validation errors recorded by save_meta_boxes(). Without this the
     * meta box silently keeps the previous values, leaving no clue as to why an
     * edit did not take.
     */
    public function show_save_errors()
    {
        $screen = get_current_screen();
        if (! $screen || DOGPED_POST_TYPE !== $screen->post_type || 'post' !== $screen->base) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only: identifies which dog's errors to display.
        $post_id = isset($_GET['post']) ? absint(wp_unslash($_GET['post'])) : 0;
        if (! $post_id) {
            return;
        }

        $errors = get_transient('dogped_save_errors_' . $post_id);
        if (empty($errors) || ! is_array($errors)) {
            return;
        }

        delete_transient('dogped_save_errors_' . $post_id);
        ?>
        <div class="notice notice-error">
            <p><strong><?php esc_html_e('The dog details were not saved:', 'spotmaniac-dog-pedigree'); ?></strong></p>
            <ul class="ul-disc">
                <?php foreach ($errors as $error) : ?>
                    <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    public function enqueue_admin_assets($hook)
    {
        $screen = get_current_screen();
        if (! $screen) {
            return;
        }

        // Dog edit screen.
        if (DOGPED_POST_TYPE === $screen->post_type && in_array($hook, array( 'post.php', 'post-new.php' ), true)) {
            wp_enqueue_media();
            wp_enqueue_script(
                'dogped-admin',
                DOGPED_URL . 'assets/js/dogped-admin.js',
                array( 'jquery', 'jquery-ui-sortable' ),
                DOGPED_VERSION,
                true
            );
            wp_localize_script('dogped-admin', 'dogpedAdmin', array(
                'selectImage'       => __('Select Dog Photo', 'spotmaniac-dog-pedigree'),
                'useImage'          => __('Use this image', 'spotmaniac-dog-pedigree'),
                'removeImage'       => __('Remove image', 'spotmaniac-dog-pedigree'),
                'searchPlaceholder' => __('Type to search for another dog', 'spotmaniac-dog-pedigree'),
                'searchNonce'       => wp_create_nonce('wp_rest'),
                'restUrl'           => rest_url('spotmaniac-dog-pedigree/v1/'),
            ));
            wp_enqueue_style(
                'dogped-admin',
                DOGPED_URL . 'assets/css/dogped-admin.css',
                array(),
                DOGPED_VERSION
            );
        }

        // Settings page.
        if (DOGPED_POST_TYPE . '_page_dogped-settings' === $screen->id) {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script(
                'dogped-admin',
                DOGPED_URL . 'assets/js/dogped-admin.js',
                array( 'jquery', 'jquery-ui-sortable' ),
                DOGPED_VERSION,
                true
            );
            wp_enqueue_style(
                'dogped-admin',
                DOGPED_URL . 'assets/css/dogped-admin.css',
                array(),
                DOGPED_VERSION
            );
        }
    }

    public function add_meta_boxes()
    {
        add_meta_box(
            'dogped-details',
            __('Dog Details', 'spotmaniac-dog-pedigree'),
            array( $this, 'render_details_meta_box' ),
            DOGPED_POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'dogped-photo',
            __('Dog Photo', 'spotmaniac-dog-pedigree'),
            array( $this, 'render_photo_meta_box' ),
            DOGPED_POST_TYPE,
            'side',
            'high'
        );

        add_meta_box(
            'dogped-parents',
            __('Parents', 'spotmaniac-dog-pedigree'),
            array( $this, 'render_parents_meta_box' ),
            DOGPED_POST_TYPE,
            'normal',
            'default'
        );

        add_meta_box(
            'dogped-owner',
            __('Owner', 'spotmaniac-dog-pedigree'),
            array( $this, 'render_owner_meta_box' ),
            DOGPED_POST_TYPE,
            'side',
            'default'
        );

        /**
         * Pro add-on registers extra meta boxes (health tests, breed taxonomy panel, etc.).
         */
        do_action('dogped_metaboxes', DOGPED_POST_TYPE);
    }

    public function render_details_meta_box($post)
    {
        wp_nonce_field('dogped_save_meta', 'dogped_meta_nonce');
        $data = Dogped_Utilities::get_dog_data($post->ID);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="dogped_name"><?php esc_html_e('Registered Name', 'spotmaniac-dog-pedigree'); ?> <span class="dogped-required">*</span></label></th>
                <td><input type="text" id="dogped_name" name="dogped_name" value="<?php echo esc_attr($data['dogped_name'] ?? ''); ?>" class="regular-text" required /></td>
            </tr>
            <tr>
                <th><label for="dogped_call_name"><?php esc_html_e('Call Name', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td><input type="text" id="dogped_call_name" name="dogped_call_name" value="<?php echo esc_attr($data['dogped_call_name'] ?? ''); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="dogped_sex"><?php esc_html_e('Sex', 'spotmaniac-dog-pedigree'); ?> <span class="dogped-required">*</span></label></th>
                <td>
                    <select id="dogped_sex" name="dogped_sex" required>
                        <option value=""><?php esc_html_e('- Select -', 'spotmaniac-dog-pedigree'); ?></option>
                        <option value="male" <?php selected($data['dogped_sex'] ?? '', 'male'); ?>><?php esc_html_e('Male', 'spotmaniac-dog-pedigree'); ?></option>
                        <option value="female" <?php selected($data['dogped_sex'] ?? '', 'female'); ?>><?php esc_html_e('Female', 'spotmaniac-dog-pedigree'); ?></option>
                    </select>
                </td>
            </tr>

            <?php
            /**
             * Pro add-on injects breed/variety fields here.
             */
            do_action('dogped_details_after_sex', $post, $data);
            ?>

            <tr>
                <th><label for="dogped_color"><?php esc_html_e('Color', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td>
                    <?php
                    $colors = Dogped_Utilities::get_field_options('color');
                    if (! empty($colors)) :
                        ?>
                        <select id="dogped_color" name="dogped_color">
                            <option value=""><?php esc_html_e('- Select -', 'spotmaniac-dog-pedigree'); ?></option>
                            <?php foreach ($colors as $color) : ?>
                                <option value="<?php echo esc_attr($color); ?>" <?php selected($data['dogped_color'] ?? '', $color); ?>><?php echo esc_html($color); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else : ?>
                        <input type="text" id="dogped_color" name="dogped_color" value="<?php echo esc_attr($data['dogped_color'] ?? ''); ?>" class="regular-text" />
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label for="dogped_size"><?php esc_html_e('Size', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td>
                    <?php
                    $sizes = Dogped_Utilities::get_field_options('size');
                    if (! empty($sizes)) :
                        ?>
                        <select id="dogped_size" name="dogped_size">
                            <option value=""><?php esc_html_e('- Select -', 'spotmaniac-dog-pedigree'); ?></option>
                            <?php foreach ($sizes as $size) : ?>
                                <option value="<?php echo esc_attr($size); ?>" <?php selected($data['dogped_size'] ?? '', $size); ?>><?php echo esc_html($size); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else : ?>
                        <input type="text" id="dogped_size" name="dogped_size" value="<?php echo esc_attr($data['dogped_size'] ?? ''); ?>" class="regular-text" />
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label for="dogped_breeding_status"><?php esc_html_e('Breeding Status', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td>
                    <?php
                    $statuses = Dogped_Utilities::get_field_options('breeding_status');
                    if (! empty($statuses)) :
                        ?>
                        <select id="dogped_breeding_status" name="dogped_breeding_status">
                            <option value=""><?php esc_html_e('- Select -', 'spotmaniac-dog-pedigree'); ?></option>
                            <?php foreach ($statuses as $status) : ?>
                                <option value="<?php echo esc_attr($status); ?>" <?php selected($data['dogped_breeding_status'] ?? '', $status); ?>><?php echo esc_html($status); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else : ?>
                        <input type="text" id="dogped_breeding_status" name="dogped_breeding_status" value="<?php echo esc_attr($data['dogped_breeding_status'] ?? ''); ?>" class="regular-text" />
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label for="dogped_birth_date"><?php esc_html_e('Birth Date', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td><input type="date" id="dogped_birth_date" name="dogped_birth_date" value="<?php echo esc_attr($data['dogped_birth_date'] ?? ''); ?>" /></td>
            </tr>
            <tr>
                <th><label for="dogped_death_date"><?php esc_html_e('Death Date', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td><input type="date" id="dogped_death_date" name="dogped_death_date" value="<?php echo esc_attr($data['dogped_death_date'] ?? ''); ?>" /></td>
            </tr>
            <tr>
                <th><label for="dogped_registration_number"><?php esc_html_e('Registration Number', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td><textarea id="dogped_registration_number" name="dogped_registration_number" rows="2" class="large-text"><?php echo esc_textarea($data['dogped_registration_number'] ?? ''); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="dogped_registration_date"><?php esc_html_e('Registration Date', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td><input type="date" id="dogped_registration_date" name="dogped_registration_date" value="<?php echo esc_attr($data['dogped_registration_date'] ?? ''); ?>" /></td>
            </tr>
            <tr>
                <th><label for="dogped_tattoo_number"><?php esc_html_e('Tattoo Number', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td><input type="text" id="dogped_tattoo_number" name="dogped_tattoo_number" value="<?php echo esc_attr($data['dogped_tattoo_number'] ?? ''); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="dogped_microchip"><?php esc_html_e('Microchip', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td><input type="text" id="dogped_microchip" name="dogped_microchip" value="<?php echo esc_attr($data['dogped_microchip'] ?? ''); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="dogped_club_number"><?php esc_html_e('Club Number', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td><input type="text" id="dogped_club_number" name="dogped_club_number" value="<?php echo esc_attr($data['dogped_club_number'] ?? ''); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="dogped_breeder"><?php esc_html_e('Breeder', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td><input type="text" id="dogped_breeder" name="dogped_breeder" value="<?php echo esc_attr($data['dogped_breeder'] ?? ''); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="dogped_titles"><?php esc_html_e('Titles', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td><textarea id="dogped_titles" name="dogped_titles" rows="3" class="large-text"><?php echo esc_textarea($data['dogped_titles'] ?? ''); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="dogped_health"><?php esc_html_e('Health', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td><textarea id="dogped_health" name="dogped_health" rows="3" class="large-text"><?php echo esc_textarea($data['dogped_health'] ?? ''); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="dogped_description"><?php esc_html_e('Description', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td><textarea id="dogped_description" name="dogped_description" rows="4" class="large-text"><?php echo esc_textarea($data['dogped_description'] ?? ''); ?></textarea></td>
            </tr>

            <?php
            $custom_fields = Dogped_Settings::get_custom_dropdown_fields();
            foreach ($custom_fields as $field) :
                $meta_key = 'dogped_custom_' . $field['slug'];
                $current  = get_post_meta($post->ID, $meta_key, true);
                $options  = Dogped_Utilities::get_field_options($field['slug']);
                ?>
            <tr>
                <th><label for="<?php echo esc_attr($meta_key); ?>"><?php echo esc_html($field['label']); ?></label></th>
                <td>
                    <?php if (! empty($options)) : ?>
                        <select id="<?php echo esc_attr($meta_key); ?>" name="<?php echo esc_attr($meta_key); ?>">
                            <option value=""><?php esc_html_e('- Select -', 'spotmaniac-dog-pedigree'); ?></option>
                            <?php foreach ($options as $opt) : ?>
                                <option value="<?php echo esc_attr($opt); ?>" <?php selected($current, $opt); ?>><?php echo esc_html($opt); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else : ?>
                        <input type="text" id="<?php echo esc_attr($meta_key); ?>" name="<?php echo esc_attr($meta_key); ?>" value="<?php echo esc_attr($current); ?>" class="regular-text" />
                        <p class="description"><?php esc_html_e('Add options in Dogs → Settings → Dropdown Field Options to show a dropdown.', 'spotmaniac-dog-pedigree'); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }

    public function render_photo_meta_box($post)
    {
        $photo_id  = get_post_meta($post->ID, 'dogped_photo', true);
        $photo_url = $photo_id ? wp_get_attachment_image_url((int) $photo_id, 'medium') : '';
        ?>
        <div id="dogped-photo-wrapper">
            <?php if ($photo_url) : ?>
                <img src="<?php echo esc_url($photo_url); ?>" style="max-width:100%;height:auto;" id="dogped-photo-preview" />
            <?php else : ?>
                <img src="" style="max-width:100%;height:auto;display:none;" id="dogped-photo-preview" />
            <?php endif; ?>
            <input type="hidden" id="dogped_photo" name="dogped_photo" value="<?php echo esc_attr($photo_id); ?>" />
            <p>
                <button type="button" class="button" id="dogped-photo-upload"><?php esc_html_e('Select Photo', 'spotmaniac-dog-pedigree'); ?></button>
                <button type="button" class="button" id="dogped-photo-remove" <?php echo $photo_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e('Remove', 'spotmaniac-dog-pedigree'); ?></button>
            </p>
        </div>
        <?php
    }

    /**
     * Renders one parent dropdown. The currently assigned parent is always
     * present as an option even when it falls outside the listed window, so
     * saving the form can never silently drop an existing parent.
     *
     * @param string $field       Meta key, e.g. 'dogped_father_id'.
     * @param string $sex         Sex to list.
     * @param int    $current_id  Currently assigned parent post ID.
     * @param string $current_name Currently assigned parent name.
     * @param int    $exclude_id  Post ID to leave out of the list.
     */
    private function render_parent_select($field, $sex, $current_id, $current_name, $exclude_id)
    {
        $options    = Dogped_Utilities::get_parent_options($sex, $exclude_id);
        $current_id = (int) $current_id;
        $listed_ids = wp_list_pluck($options, 'ID');
        ?>
        <select id="<?php echo esc_attr($field); ?>" name="<?php echo esc_attr($field); ?>" class="dogped-parent-select" data-sex="<?php echo esc_attr($sex); ?>" data-exclude="<?php echo esc_attr($exclude_id); ?>" style="width:100%;">
            <option value=""><?php esc_html_e('- None -', 'spotmaniac-dog-pedigree'); ?></option>
            <?php if ($current_id && ! in_array($current_id, $listed_ids, true)) : ?>
                <option value="<?php echo esc_attr($current_id); ?>" selected>
                    <?php echo esc_html($current_name); ?> (#<?php echo esc_html($current_id); ?>)
                </option>
            <?php endif; ?>
            <?php foreach ($options as $dog) : ?>
                <option value="<?php echo esc_attr($dog->ID); ?>" <?php selected($current_id, $dog->ID); ?>>
                    <?php echo esc_html($dog->post_title); ?> (#<?php echo esc_html($dog->ID); ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function render_parents_meta_box($post)
    {
        $data = Dogped_Utilities::get_dog_data($post->ID);

        $note = sprintf(
            /* translators: %d: number of dogs listed in the dropdown. */
            __('The list shows the %d most recently added dogs. Use the box below to search for any other dog.', 'spotmaniac-dog-pedigree'),
            Dogped_Utilities::PARENT_LIST_LIMIT
        );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="dogped_father_id"><?php esc_html_e('Father (Sire)', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td>
                    <?php
                    $this->render_parent_select(
                        'dogped_father_id',
                        'male',
                        $data['dogped_father_id'] ?? 0,
                        $data['dogped_father_name'] ?? '',
                        $post->ID
                    );
                    ?>
                    <p class="description"><?php echo esc_html($note); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="dogped_mother_id"><?php esc_html_e('Mother (Dam)', 'spotmaniac-dog-pedigree'); ?></label></th>
                <td>
                    <?php
                    $this->render_parent_select(
                        'dogped_mother_id',
                        'female',
                        $data['dogped_mother_id'] ?? 0,
                        $data['dogped_mother_name'] ?? '',
                        $post->ID
                    );
                    ?>
                    <p class="description"><?php echo esc_html($note); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    public function render_owner_meta_box($post)
    {
        $data       = Dogped_Utilities::get_dog_data($post->ID);
        $owner_id   = $data['dogped_owner_id'] ?? '';
        $owner_name = '';
        if ($owner_id) {
            $user = get_user_by('ID', $owner_id);
            if ($user) {
                $owner_name = $user->display_name;
            }
        }
        ?>
        <p>
            <label for="dogped_owner"><?php esc_html_e('Owner Name', 'spotmaniac-dog-pedigree'); ?></label><br />
            <input type="text" id="dogped_owner" name="dogped_owner" value="<?php echo esc_attr($data['dogped_owner'] ?? ''); ?>" class="widefat" />
        </p>
        <p>
            <label for="dogped_owner_id"><?php esc_html_e('Owner User ID', 'spotmaniac-dog-pedigree'); ?></label><br />
            <input type="number" id="dogped_owner_id" name="dogped_owner_id" value="<?php echo esc_attr($owner_id); ?>" class="widefat" />
            <?php if ($owner_name) : ?>
                <span class="description"><?php echo esc_html($owner_name); ?></span>
            <?php endif; ?>
        </p>
        <?php
    }

    public function save_meta_boxes($post_id, $post)
    {
        if (! isset($_POST['dogped_meta_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dogped_meta_nonce'])), 'dogped_save_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        $data = array();
        foreach (Dogped_Utilities::META_FIELDS as $key) {
            if (isset($_POST[ $key ])) {
                $data[ $key ] = sanitize_text_field(wp_unslash($_POST[ $key ]));
            }
        }

        $validated = Dogped_Validator::validate($data, $post_id);
        if (is_wp_error($validated)) {
            set_transient('dogped_save_errors_' . $post_id, $validated->get_error_messages(), 30);
            return;
        }

        if (! empty($validated['dogped_name']) && $post->post_title !== $validated['dogped_name']) {
            remove_action('save_post_' . DOGPED_POST_TYPE, array( $this, 'save_meta_boxes' ), 10);
            wp_update_post(array(
                'ID'         => $post_id,
                'post_title' => $validated['dogped_name'],
                'post_name'  => sanitize_title($validated['dogped_name']),
            ));
            add_action('save_post_' . DOGPED_POST_TYPE, array( $this, 'save_meta_boxes' ), 10, 2);
        }

        Dogped_Utilities::save_dog_meta($post_id, $validated);

        $custom_fields = Dogped_Settings::get_custom_dropdown_fields();
        foreach ($custom_fields as $field) {
            $meta_key = 'dogped_custom_' . $field['slug'];
            if (isset($_POST[ $meta_key ])) {
                $value = sanitize_text_field(wp_unslash($_POST[ $meta_key ]));
                if ('' === $value) {
                    delete_post_meta($post_id, $meta_key);
                } else {
                    update_post_meta($post_id, $meta_key, $value);
                }
            }
        }

        /**
         * Pro add-on hooks here to save its own meta box data (health tests, etc.).
         */
        do_action('dogped_save_metaboxes', $post_id, $post);
    }

    public function custom_columns($columns)
    {
        $new = array();
        $new['cb']            = $columns['cb'];
        $new['dogped_photo']  = __('Photo', 'spotmaniac-dog-pedigree');
        $new['title']         = $columns['title'];
        $new['dogped_sex']    = __('Sex', 'spotmaniac-dog-pedigree');
        $new['dogped_color']  = __('Color', 'spotmaniac-dog-pedigree');
        $new['dogped_birth']  = __('Birth Date', 'spotmaniac-dog-pedigree');
        $new['dogped_reg']    = __('Reg. Number', 'spotmaniac-dog-pedigree');
        $new['date']          = $columns['date'];
        return $new;
    }

    public function render_column($column, $post_id)
    {
        $data = Dogped_Utilities::get_dog_data($post_id);
        switch ($column) {
            case 'dogped_photo':
                if (! empty($data['dogped_photo_thumb'])) {
                    printf('<img src="%s" style="width:50px;height:50px;object-fit:cover;border-radius:4px;" alt="" />', esc_url($data['dogped_photo_thumb']));
                } else {
                    echo '<span class="dashicons dashicons-format-image" style="color:#ccc;font-size:30px;"></span>';
                }
                break;
            case 'dogped_sex':
                echo esc_html(ucfirst($data['dogped_sex'] ?? ''));
                break;
            case 'dogped_color':
                echo esc_html($data['dogped_color'] ?? '');
                break;
            case 'dogped_birth':
                echo esc_html(Dogped_Utilities::format_date($data['dogped_birth_date'] ?? ''));
                break;
            case 'dogped_reg':
                echo esc_html($data['dogped_registration_number'] ?? '');
                break;
        }
    }
}
