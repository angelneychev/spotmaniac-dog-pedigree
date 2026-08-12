<?php

/**
 * REST API endpoints - namespace spotmaniac-dog-pedigree/v1.
 *
 * Permission callbacks verify both user capability AND that the target post
 * is actually a dog post type, to prevent the WordPress.org reviewer concerns
 * about callbacks operating on arbitrary post IDs.
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dogped_API
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
        add_action('rest_api_init', array( $this, 'register_routes' ));
    }

    public function register_routes()
    {
        $namespace = 'spotmaniac-dog-pedigree/v1';

        register_rest_route($namespace, '/dog/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_dog' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'id' => array(
                    'validate_callback' => function ($param) {
                        return is_numeric($param);
                    },
                ),
            ),
        ));

        register_rest_route($namespace, '/search', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'search_dogs' ),
            'permission_callback' => '__return_true',
        ));

        register_rest_route($namespace, '/dog', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'create_dog' ),
            'permission_callback' => array( $this, 'can_create_dogs' ),
        ));

        register_rest_route($namespace, '/dog/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'update_dog' ),
            'permission_callback' => array( $this, 'can_edit_specific_dog' ),
        ));

        register_rest_route($namespace, '/dog/(?P<id>\d+)/owner-update', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'owner_update_dog' ),
            'permission_callback' => array( $this, 'is_dog_owner' ),
        ));

        register_rest_route($namespace, '/dog/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => array( $this, 'delete_dog' ),
            'permission_callback' => array( $this, 'can_delete_specific_dog' ),
        ));

        register_rest_route($namespace, '/search-parents', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'search_parents' ),
            'permission_callback' => array( $this, 'can_create_dogs' ),
        ));

        /**
         * Pro add-on registers its own REST routes here (still under same namespace).
         */
        do_action('dogped_register_rest_routes', $namespace);
    }

    // =========================================================================
    // Permission callbacks
    // =========================================================================

    /**
     * Creating a published dog post requires both edit_posts AND publish_posts.
     * Without publish_posts, the user could only create drafts via the normal
     * admin UI; the REST endpoint mirrors that constraint.
     */
    public function can_create_dogs()
    {
        return current_user_can('edit_posts') && current_user_can('publish_posts');
    }

    /**
     * Updating a specific dog requires edit_post on that ID AND that the post
     * is actually a dog (prevents writing dogped_ meta keys onto other posts).
     */
    public function can_edit_specific_dog($request)
    {
        $post_id = (int) $request['id'];
        if (DOGPED_POST_TYPE !== get_post_type($post_id)) {
            return false;
        }
        return current_user_can('edit_post', $post_id);
    }

    /**
     * Deleting a specific dog requires delete_post on that ID AND that the post
     * is actually a dog (prevents trashing arbitrary deletable posts).
     */
    public function can_delete_specific_dog($request)
    {
        $post_id = (int) $request['id'];
        if (DOGPED_POST_TYPE !== get_post_type($post_id)) {
            return false;
        }
        return current_user_can('delete_post', $post_id);
    }

    /**
     * Owner-update requires login, that the target IS a dog, and that the
     * current user is either the recorded owner or has edit_post capability.
     */
    public function is_dog_owner($request)
    {
        if (! is_user_logged_in()) {
            return false;
        }
        $post_id = (int) $request['id'];
        if (DOGPED_POST_TYPE !== get_post_type($post_id)) {
            return false;
        }
        $owner_id = (int) get_post_meta($post_id, 'dogped_owner_id', true);
        return $owner_id === get_current_user_id() || current_user_can('edit_post', $post_id);
    }

    // =========================================================================
    // Endpoint handlers
    // =========================================================================

    public function get_dog($request)
    {
        $post_id = (int) $request['id'];
        $post    = get_post($post_id);

        if (! $post || DOGPED_POST_TYPE !== $post->post_type || 'publish' !== $post->post_status) {
            return new WP_Error('not_found', __('Dog not found.', 'spotmaniac-dog-pedigree'), array( 'status' => 404 ));
        }

        $data = Dogped_Utilities::get_dog_data($post_id);

        if (! current_user_can('edit_posts')) {
            $data = Dogped_Utilities::filter_public_fields($data);
        }

        return rest_ensure_response($data);
    }

    public function search_dogs($request)
    {
        $params = Dogped_Validator::validate_search($request->get_params());

        $query_args = array(
            'post_type'      => DOGPED_POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => $params['per_page'],
            'paged'          => $params['paged'],
            'orderby'        => $params['orderby'],
            'order'          => $params['order'],
        );

        if (! empty($params['s'])) {
            $query_args['s'] = $params['s'];
        }

        $meta_query = array();
        if (! empty($params['sex'])) {
            $meta_query[] = array( 'key' => 'dogped_sex', 'value' => $params['sex'] );
        }
        if (! empty($params['color'])) {
            $meta_query[] = array( 'key' => 'dogped_color', 'value' => $params['color'] );
        }
        if (! empty($params['breeding_status'])) {
            $meta_query[] = array( 'key' => 'dogped_breeding_status', 'value' => $params['breeding_status'] );
        }
        if (! empty($meta_query)) {
            $query_args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery
        }

        $query   = new WP_Query($query_args);
        $results = array();

        foreach ($query->posts as $post) {
            $dog       = Dogped_Utilities::get_dog_data($post->ID);
            $results[] = Dogped_Utilities::filter_public_fields($dog);
        }

        return rest_ensure_response(array(
            'dogs'  => $results,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'page'  => $params['paged'],
        ));
    }

    public function create_dog($request)
    {
        $data = $request->get_json_params();
        if (empty($data)) {
            $data = $request->get_params();
        }

        $validated = Dogped_Validator::validate($data);
        if (is_wp_error($validated)) {
            return $validated;
        }

        $post_id = wp_insert_post(array(
            'post_type'   => DOGPED_POST_TYPE,
            'post_title'  => $validated['dogped_name'],
            'post_status' => 'publish',
        ));

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        Dogped_Utilities::save_dog_meta($post_id, $validated);

        return rest_ensure_response(array(
            'id'      => $post_id,
            'message' => __('Dog created successfully.', 'spotmaniac-dog-pedigree'),
        ));
    }

    public function update_dog($request)
    {
        $post_id = (int) $request['id'];

        $data = $request->get_json_params();
        if (empty($data)) {
            $data = $request->get_params();
        }

        $validated = Dogped_Validator::validate($data, $post_id);
        if (is_wp_error($validated)) {
            return $validated;
        }

        if (! empty($validated['dogped_name'])) {
            wp_update_post(array(
                'ID'         => $post_id,
                'post_title' => $validated['dogped_name'],
                'post_name'  => sanitize_title($validated['dogped_name']),
            ));
        }

        Dogped_Utilities::save_dog_meta($post_id, $validated);

        return rest_ensure_response(array(
            'id'      => $post_id,
            'message' => __('Dog updated successfully.', 'spotmaniac-dog-pedigree'),
        ));
    }

    public function owner_update_dog($request)
    {
        $post_id = (int) $request['id'];
        $data    = $request->get_json_params();
        if (empty($data)) {
            $data = $request->get_params();
        }

        $filtered = array();
        foreach (Dogped_Utilities::OWNER_EDITABLE_FIELDS as $field) {
            if (isset($data[ $field ])) {
                $filtered[ $field ] = $data[ $field ];
            }
        }

        if (empty($filtered)) {
            return new WP_Error('no_fields', __('No editable fields provided.', 'spotmaniac-dog-pedigree'), array( 'status' => 400 ));
        }

        $clean = array();
        foreach ($filtered as $key => $value) {
            if ('dogped_photo' === $key) {
                $clean[ $key ] = absint($value);
            } else {
                $clean[ $key ] = sanitize_textarea_field($value);
            }
        }

        Dogped_Utilities::save_dog_meta($post_id, $clean);

        return rest_ensure_response(array(
            'id'      => $post_id,
            'message' => __('Dog updated successfully.', 'spotmaniac-dog-pedigree'),
        ));
    }

    public function delete_dog($request)
    {
        $post_id = (int) $request['id'];
        $result  = wp_trash_post($post_id);

        if (! $result) {
            return new WP_Error('delete_failed', __('Could not delete dog.', 'spotmaniac-dog-pedigree'), array( 'status' => 500 ));
        }

        return rest_ensure_response(array( 'deleted' => true ));
    }

    public function search_parents($request)
    {
        $search  = sanitize_text_field($request->get_param('s'));
        $sex     = sanitize_text_field($request->get_param('sex'));
        $exclude = absint($request->get_param('exclude'));

        if (mb_strlen($search) < 2) {
            return rest_ensure_response(array());
        }

        $query_args = array(
            'post_type'      => DOGPED_POST_TYPE,
            'post_status'    => 'publish',
            's'              => $search,
            'posts_per_page' => 20,
        );

        // Exclude the dog being edited so it can never be listed as its own parent.
        if ($exclude) {
            $query_args['post__not_in'] = array( $exclude );
        }

        if ($sex && in_array($sex, array( 'male', 'female' ), true)) {
            $query_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
                array( 'key' => 'dogped_sex', 'value' => $sex ),
            );
        }

        $query   = new WP_Query($query_args);
        $results = array();

        foreach ($query->posts as $post) {
            $results[] = array(
                'id'   => $post->ID,
                'name' => $post->post_title,
                'text' => $post->post_title . ' (#' . $post->ID . ')',
            );
        }

        return rest_ensure_response($results);
    }
}
