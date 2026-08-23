<?php

/**
 * Plugin Name: Spotmaniac Dog Pedigree
 * Plugin URI:  https://dogspedigree.lemonsqueezy.com/
 * Description: A dog catalog plugin for breeders. Custom post type with 22 fields, public catalog with filters, single dog pages with photo and key info, owner self-edit, and template overrides.
 * Version:     1.0.4
 * Author:      Angel Neychev
 * Author URI:  https://spotmaniac.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: spotmaniac-dog-pedigree
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('ABSPATH')) {
    exit;
}

// Plugin constants.
define('DOGPED_VERSION', '1.0.4');
define('DOGPED_FILE', __FILE__);
define('DOGPED_PATH', plugin_dir_path(__FILE__));
define('DOGPED_URL', plugin_dir_url(__FILE__));
define('DOGPED_BASENAME', plugin_basename(__FILE__));
define('DOGPED_POST_TYPE', 'dogped_dog');

// Load includes.
require_once DOGPED_PATH . 'includes/class-dogped-plugin.php';
require_once DOGPED_PATH . 'includes/class-dogped-cpt.php';
require_once DOGPED_PATH . 'includes/class-dogped-utilities.php';
require_once DOGPED_PATH . 'includes/class-dogped-validator.php';
require_once DOGPED_PATH . 'includes/class-dogped-template.php';
require_once DOGPED_PATH . 'includes/class-dogped-settings.php';
require_once DOGPED_PATH . 'includes/class-dogped-admin.php';
require_once DOGPED_PATH . 'includes/class-dogped-api.php';
require_once DOGPED_PATH . 'includes/class-dogped-help.php';

/**
 * Bootstrap plugin.
 */
function dogped_init()
{
    Dogped_Plugin::get_instance();
}
add_action('plugins_loaded', 'dogped_init');

/**
 * Fetch a plugin option with default fallback. Convenience wrapper around get_option()
 * with the dogped_ prefix automatically applied.
 *
 * @param string $key     Option suffix (without prefix).
 * @param mixed  $default Default value.
 * @return mixed
 */
function dogped_get_option($key, $default = '')
{
    return get_option('dogped_' . $key, $default);
}

/**
 * Activation hook - register CPT and seed default options so flush works correctly.
 */
function dogped_activate()
{
    Dogped_CPT::get_instance()->register_post_type();
    dogped_set_default_options();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'dogped_activate');

/**
 * Deactivation hook - flush rewrite rules.
 */
function dogped_deactivate()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'dogped_deactivate');

/**
 * Seed default options on activation.
 */
function dogped_set_default_options()
{
    $defaults = array(
        'dogped_url_prefix'              => 'dogs',
        'dogped_catalog_count'           => 12,
        'dogped_catalog_orderby'         => 'title',
        'dogped_photo_max_size'          => 5,
        'dogped_show_registration_number' => 'yes',
        'dogped_show_registration_date'  => 'yes',
        'dogped_show_tattoo'             => 'yes',
        'dogped_show_microchip'          => 'yes',
        'dogped_show_club_number'        => 'yes',
        'dogped_show_breeder'            => 'yes',
        'dogped_show_health'             => 'yes',
        'dogped_show_birth_date'         => 'yes',
        'dogped_show_death_date'         => 'yes',
        'dogped_show_titles'             => 'yes',
        'dogped_show_description'        => 'yes',
        'dogped_section_order'           => wp_json_encode(array(
            'photo',
            'basic_info',
            'identification',
            'health',
            'siblings',
        )),
    );

    foreach ($defaults as $key => $value) {
        if (get_option($key) === false) {
            update_option($key, $value);
        }
    }
}
