<?php

/**
 * Main plugin orchestrator.
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dogped_Plugin
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
        Dogped_CPT::get_instance();
        Dogped_Template::get_instance();
        Dogped_Settings::get_instance();
        Dogped_Admin::get_instance();
        Dogped_API::get_instance();
        Dogped_Help::get_instance();

        // Translations are loaded automatically by WordPress since 4.6 when the plugin
        // is hosted on WordPress.org. No need for load_plugin_textdomain() here.

        /**
         * Fires after the Free plugin has finished bootstrapping.
         * Pro add-on plugins should hook here to register their components.
         */
        do_action('dogped_loaded');
    }
}
