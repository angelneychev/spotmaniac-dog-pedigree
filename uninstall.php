<?php

/**
 * Uninstall script - clean up all plugin data.
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all dog posts and their meta.
$dogped_dogs = get_posts(array(
    'post_type'      => 'dogped_dog',
    'posts_per_page' => -1,
    'post_status'    => 'any',
    'fields'         => 'ids',
));

foreach ($dogped_dogs as $dogped_dog_id) {
    wp_delete_post($dogped_dog_id, true);
}

// Delete all plugin options.
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'dogped\\_%'"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

// Delete transients.
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%\\_transient\\_dogped\\_%'"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

// Flush rewrite rules.
flush_rewrite_rules();
