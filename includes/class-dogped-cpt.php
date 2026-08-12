<?php

/**
 * Custom Post Type registration.
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dogped_CPT
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
        add_action('init', array( $this, 'register_post_type' ));
    }

    public function register_post_type()
    {
        $url_prefix = dogped_get_option('url_prefix', 'dogs');

        $labels = array(
            'name'               => __('Dogs', 'spotmaniac-dog-pedigree'),
            'singular_name'      => __('Dog', 'spotmaniac-dog-pedigree'),
            'add_new'            => __('Add New Dog', 'spotmaniac-dog-pedigree'),
            'add_new_item'       => __('Add New Dog', 'spotmaniac-dog-pedigree'),
            'edit_item'          => __('Edit Dog', 'spotmaniac-dog-pedigree'),
            'new_item'           => __('New Dog', 'spotmaniac-dog-pedigree'),
            'view_item'          => __('View Dog', 'spotmaniac-dog-pedigree'),
            'search_items'       => __('Search Dogs', 'spotmaniac-dog-pedigree'),
            'not_found'          => __('No dogs found', 'spotmaniac-dog-pedigree'),
            'not_found_in_trash' => __('No dogs found in trash', 'spotmaniac-dog-pedigree'),
            'all_items'          => __('All Dogs', 'spotmaniac-dog-pedigree'),
            'menu_name'          => __('Dogs', 'spotmaniac-dog-pedigree'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => false,
            'rewrite'            => array(
                'slug'       => sanitize_title($url_prefix),
                'with_front' => false,
            ),
            'supports'           => array( 'title', 'thumbnail' ),
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'menu_icon'          => 'dashicons-pets',
            'menu_position'      => 25,
        );

        register_post_type(DOGPED_POST_TYPE, $args);

        /**
         * Fires after the dog post type has been registered.
         * Pro add-on uses this to register the breed taxonomy.
         */
        do_action('dogped_after_register_post_type', DOGPED_POST_TYPE);
    }
}
