<?php

/**
 * Utility helpers - meta IO, formatting, derived data.
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dogped_Utilities
{
    /**
     * How many dogs the Father / Mother dropdowns list, in the admin meta box and
     * in the Pro submission form alike. Older dogs stay reachable via search.
     */
    const PARENT_LIST_LIMIT = 50;

    /**
     * All 22 dog meta field keys. Stored in post_meta with the same key.
     *
     * @var string[]
     */
    const META_FIELDS = array(
        'dogped_name',
        'dogped_call_name',
        'dogped_sex',
        'dogped_breeder',
        'dogped_owner',
        'dogped_color',
        'dogped_size',
        'dogped_breeding_status',
        'dogped_titles',
        'dogped_health',
        'dogped_birth_date',
        'dogped_death_date',
        'dogped_registration_number',
        'dogped_registration_date',
        'dogped_tattoo_number',
        'dogped_microchip',
        'dogped_club_number',
        'dogped_description',
        'dogped_photo',
        'dogped_father_id',
        'dogped_mother_id',
        'dogped_owner_id',
    );

    /**
     * Fields safe for anonymous public display.
     *
     * @var string[]
     */
    const PUBLIC_FIELDS = array(
        'dogped_name',
        'dogped_call_name',
        'dogped_sex',
        'dogped_breeder',
        'dogped_color',
        'dogped_size',
        'dogped_breeding_status',
        'dogped_titles',
        'dogped_health',
        'dogped_birth_date',
        'dogped_death_date',
        'dogped_registration_number',
        'dogped_registration_date',
        'dogped_description',
        'dogped_photo',
        'dogped_father_id',
        'dogped_mother_id',
    );

    /**
     * Fields an owner may edit on their own dog (limited subset).
     *
     * @var string[]
     */
    const OWNER_EDITABLE_FIELDS = array(
        'dogped_photo',
        'dogped_titles',
        'dogped_color',
        'dogped_call_name',
        'dogped_description',
    );

    public static function get_dog_data($dog_id)
    {
        $cache_key = 'dogped_data_' . $dog_id;
        $data      = wp_cache_get($cache_key, 'dogped');

        if (false !== $data) {
            return $data;
        }

        $data = array( 'ID' => $dog_id );
        foreach (self::META_FIELDS as $key) {
            $data[ $key ] = get_post_meta($dog_id, $key, true);
        }

        if (! empty($data['dogped_name'])) {
            $data['dogped_name'] = html_entity_decode($data['dogped_name'], ENT_QUOTES, 'UTF-8');
        }

        if (! empty($data['dogped_photo'])) {
            $data['dogped_photo_url']   = wp_get_attachment_image_url((int) $data['dogped_photo'], 'large');
            $data['dogped_photo_thumb'] = wp_get_attachment_image_url((int) $data['dogped_photo'], 'medium');
        }

        if (! empty($data['dogped_father_id'])) {
            $data['dogped_father_name'] = get_the_title((int) $data['dogped_father_id']);
        }
        if (! empty($data['dogped_mother_id'])) {
            $data['dogped_mother_name'] = get_the_title((int) $data['dogped_mother_id']);
        }

        /**
         * Allows Pro add-ons to enrich the dog data array with extra fields
         * (e.g. health tests, breed taxonomy terms).
         */
        $data = apply_filters('dogped_dog_data', $data, $dog_id);

        wp_cache_set($cache_key, $data, 'dogped', 300);

        return $data;
    }

    public static function filter_public_fields($data)
    {
        $filtered = array( 'ID' => $data['ID'] ?? 0 );
        foreach (self::PUBLIC_FIELDS as $key) {
            if (isset($data[ $key ])) {
                $filtered[ $key ] = $data[ $key ];
            }
        }
        foreach (array( 'dogped_photo_url', 'dogped_photo_thumb', 'dogped_father_name', 'dogped_mother_name' ) as $key) {
            if (isset($data[ $key ])) {
                $filtered[ $key ] = $data[ $key ];
            }
        }
        /**
         * Filter the public-safe dog data returned to anonymous REST callers.
         */
        return apply_filters('dogped_public_dog_data', $filtered, $data);
    }

    public static function save_dog_meta($post_id, $data)
    {
        foreach (self::META_FIELDS as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $value = $data[ $key ];
            if ('' === $value || null === $value) {
                delete_post_meta($post_id, $key);
            } else {
                update_post_meta($post_id, $key, $value);
            }
        }
        wp_cache_delete('dogped_data_' . $post_id, 'dogped');

        /**
         * Fires after the standard dog meta has been saved.
         * Pro add-ons hook here to save their own custom meta (health tests, etc.).
         */
        do_action('dogped_after_save_dog_meta', $post_id, $data);
    }

    public static function format_date($date, $format = 'd.m.Y')
    {
        if (empty($date)) {
            return '';
        }
        $timestamp = strtotime($date);
        if (false === $timestamp) {
            return esc_html($date);
        }
        return date_i18n($format, $timestamp);
    }

    /**
     * Get siblings (same father AND/OR same mother).
     *
     * @param int $dog_id Dog post ID.
     * @return WP_Post[]
     */
    public static function get_siblings($dog_id)
    {
        $data      = self::get_dog_data($dog_id);
        $father_id = ! empty($data['dogped_father_id']) ? (int) $data['dogped_father_id'] : 0;
        $mother_id = ! empty($data['dogped_mother_id']) ? (int) $data['dogped_mother_id'] : 0;

        if (! $father_id && ! $mother_id) {
            return array();
        }

        $meta_query = array( 'relation' => 'AND' );
        if ($father_id) {
            $meta_query[] = array( 'key' => 'dogped_father_id', 'value' => $father_id );
        }
        if ($mother_id) {
            $meta_query[] = array( 'key' => 'dogped_mother_id', 'value' => $mother_id );
        }

        $query = new WP_Query(array(
            'post_type'      => DOGPED_POST_TYPE,
            'posts_per_page' => 50,
            // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Intentional: exclude only the current dog (1 ID); the perf hit is negligible and the alternative (filtering in PHP after the query) is worse.
            'post__not_in'   => array( $dog_id ),
            'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery
        ));

        return $query->posts;
    }

    /**
     * The most recently added published dogs of one sex, for the Father / Mother
     * pickers. The dog being edited is excluded so it can never be offered as its
     * own parent; a dog being created is still an auto-draft and therefore falls
     * outside the 'publish' status anyway.
     *
     * Anything older than the returned window stays reachable through the search
     * box that sits next to the dropdown.
     *
     * @param string $sex        'male' for sires, 'female' for dams.
     * @param int    $exclude_id Post ID to leave out, 0 for none.
     * @param int    $limit      How many dogs to return.
     * @return WP_Post[]
     */
    public static function get_parent_options($sex, $exclude_id = 0, $limit = self::PARENT_LIST_LIMIT)
    {
        $query_args = array(
            'post_type'      => DOGPED_POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => (int) $limit,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
            'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
                array( 'key' => 'dogped_sex', 'value' => $sex ),
            ),
        );

        if ($exclude_id) {
            // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Intentional: excludes only the current dog (1 ID).
            $query_args['post__not_in'] = array( (int) $exclude_id );
        }

        $query = new WP_Query($query_args);

        return $query->posts;
    }

    /**
     * Whether $needle_id appears anywhere above $start_id in the pedigree.
     *
     * Used to refuse a parent link that would close a loop: if the dog being
     * edited is already an ancestor of the dog picked as its parent, then the
     * two would end up descending from each other.
     *
     * Walks both parent lines generation by generation. A visited set makes each
     * dog count once, which both bounds the work by the number of dogs involved
     * and keeps the walk terminating even if the stored data already contains a
     * loop from an earlier version. The generation cap is a second backstop.
     *
     * @param int $start_id        Dog to walk up from.
     * @param int $needle_id       Dog being looked for among the ancestors.
     * @param int $max_generations Depth limit.
     * @return bool
     */
    public static function has_ancestor($start_id, $needle_id, $max_generations = 50)
    {
        $start_id  = (int) $start_id;
        $needle_id = (int) $needle_id;

        if (! $start_id || ! $needle_id) {
            return false;
        }

        $queue       = array( $start_id );
        $visited     = array();
        $generations = 0;

        while (! empty($queue) && $generations < $max_generations) {
            $next = array();

            foreach ($queue as $dog_id) {
                if (isset($visited[ $dog_id ])) {
                    continue;
                }
                $visited[ $dog_id ] = true;

                foreach (array( 'dogped_father_id', 'dogped_mother_id' ) as $key) {
                    $parent_id = (int) get_post_meta($dog_id, $key, true);

                    if (! $parent_id || isset($visited[ $parent_id ])) {
                        continue;
                    }
                    if ($parent_id === $needle_id) {
                        return true;
                    }
                    $next[] = $parent_id;
                }
            }

            $queue = $next;
            $generations++;
        }

        return false;
    }

    /**
     * Resolve dropdown options for a field. Looks up dogped_{field}_options first,
     * then dogped_custom_{field}_options for admin-defined custom fields.
     *
     * @param string $field Field key.
     * @return array
     */
    public static function get_field_options($field)
    {
        $raw = get_option('dogped_' . $field . '_options', '');

        if ('' === $raw || '[]' === $raw) {
            $raw = get_option('dogped_custom_' . $field . '_options', '');
        }

        return Dogped_Settings::decode_repeater($raw);
    }

    /**
     * Return the current values of all custom dropdown fields for a dog.
     */
    public static function get_custom_field_values($dog_id)
    {
        $definitions = Dogped_Settings::get_custom_dropdown_fields();
        $result      = array();
        foreach ($definitions as $field) {
            $meta_key = 'dogped_custom_' . $field['slug'];
            $result[] = array(
                'label' => $field['label'],
                'slug'  => $field['slug'],
                'value' => get_post_meta($dog_id, $meta_key, true),
            );
        }
        return $result;
    }
}
