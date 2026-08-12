<?php

/**
 * Help page - quick reference for shortcodes, URL filters, REST API,
 * and template overrides.
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dogped_Help
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
        add_action('admin_menu', array( $this, 'add_help_page' ));
    }

    public function add_help_page()
    {
        add_submenu_page(
            'edit.php?post_type=' . DOGPED_POST_TYPE,
            __('Spotmaniac Dog Pedigree Help', 'spotmaniac-dog-pedigree'),
            __('Help', 'spotmaniac-dog-pedigree'),
            'edit_posts',
            'dogped-help',
            array( $this, 'render' )
        );
    }

    public function render()
    {
        $catalog_url = get_post_type_archive_link(DOGPED_POST_TYPE);
        $rest_root   = rest_url('spotmaniac-dog-pedigree/v1/');
        ?>
        <div class="wrap dogped-help">
            <h1><?php esc_html_e('Spotmaniac Dog Pedigree - Help', 'spotmaniac-dog-pedigree'); ?></h1>

            <p class="description" style="font-size:14px;max-width:800px;">
                <?php esc_html_e('A quick reference for embedding dogs on your pages, filtering the catalog, overriding templates, and using the REST API.', 'spotmaniac-dog-pedigree'); ?>
            </p>

            <!-- ============================================================ -->
            <h2><?php esc_html_e('Quick start', 'spotmaniac-dog-pedigree'); ?></h2>
            <ol>
                <li><?php esc_html_e('Add a dog from Dogs → Add New Dog. Fill in name and sex (required), then any other fields you have.', 'spotmaniac-dog-pedigree'); ?></li>
                <li>
                    <?php
                    printf(
                        /* translators: %s: catalog URL */
                        esc_html__('Visit your catalog at %s - it works without creating any pages.', 'spotmaniac-dog-pedigree'),
                        '<a href="' . esc_url($catalog_url) . '" target="_blank"><code>' . esc_html($catalog_url) . '</code></a>'
                    );
                    ?>
                </li>
                <li><?php esc_html_e('Configure dropdowns (color, size, breeding status) and field visibility in Dogs → Settings.', 'spotmaniac-dog-pedigree'); ?></li>
                <li><?php esc_html_e('To embed dogs anywhere else on your site, use one of the shortcodes below.', 'spotmaniac-dog-pedigree'); ?></li>
            </ol>

            <!-- ============================================================ -->
            <h2><?php esc_html_e('Shortcodes', 'spotmaniac-dog-pedigree'); ?></h2>

            <h3><code>[dogped-catalog]</code></h3>
            <p><?php esc_html_e('Embeds a filterable catalog grid on any page or post.', 'spotmaniac-dog-pedigree'); ?></p>

            <table class="widefat striped" style="max-width:900px;">
                <thead>
                    <tr>
                        <th style="width:160px;"><?php esc_html_e('Attribute', 'spotmaniac-dog-pedigree'); ?></th>
                        <th style="width:120px;"><?php esc_html_e('Default', 'spotmaniac-dog-pedigree'); ?></th>
                        <th><?php esc_html_e('Description', 'spotmaniac-dog-pedigree'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>count</code></td>
                        <td><code><?php echo esc_html(dogped_get_option('catalog_count', 12)); ?></code></td>
                        <td><?php esc_html_e('Number of dogs per page.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>sex</code></td>
                        <td><em><?php esc_html_e('all', 'spotmaniac-dog-pedigree'); ?></em></td>
                        <td><?php esc_html_e('Limit to one sex. Accepts: male, female.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>orderby</code></td>
                        <td><code><?php echo esc_html(dogped_get_option('catalog_orderby', 'title')); ?></code></td>
                        <td><?php esc_html_e('Sort order. Accepts: title, date, modified.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>show_filters</code></td>
                        <td><code>yes</code></td>
                        <td><?php esc_html_e('Show the search/filter form above the grid. Set to "no" to hide.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                </tbody>
            </table>

            <p><strong><?php esc_html_e('Examples:', 'spotmaniac-dog-pedigree'); ?></strong></p>
            <pre style="background:#f6f7f7;padding:12px;border-left:4px solid #2271b1;">[dogped-catalog]
[dogped-catalog count="6" sex="female"]
[dogped-catalog show_filters="no" orderby="date"]</pre>

            <h3 style="margin-top:30px;"><code>[dogped-featured]</code></h3>
            <p><?php esc_html_e('Shows a random selection of dogs (no filter form). Useful on a homepage.', 'spotmaniac-dog-pedigree'); ?></p>

            <table class="widefat striped" style="max-width:900px;">
                <thead>
                    <tr>
                        <th style="width:160px;"><?php esc_html_e('Attribute', 'spotmaniac-dog-pedigree'); ?></th>
                        <th style="width:120px;"><?php esc_html_e('Default', 'spotmaniac-dog-pedigree'); ?></th>
                        <th><?php esc_html_e('Description', 'spotmaniac-dog-pedigree'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>count</code></td>
                        <td><code>6</code></td>
                        <td><?php esc_html_e('Number of dogs to show.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>sex</code></td>
                        <td><em><?php esc_html_e('all', 'spotmaniac-dog-pedigree'); ?></em></td>
                        <td><?php esc_html_e('Limit to one sex. Accepts: male, female.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                </tbody>
            </table>

            <p><strong><?php esc_html_e('Examples:', 'spotmaniac-dog-pedigree'); ?></strong></p>
            <pre style="background:#f6f7f7;padding:12px;border-left:4px solid #2271b1;">[dogped-featured]
[dogped-featured count="3" sex="male"]</pre>

            <?php
            /**
             * Pro add-on appends its own shortcodes here.
             */
            do_action('dogped_help_shortcodes');
            ?>

            <!-- ============================================================ -->
            <h2><?php esc_html_e('URL filters on the catalog', 'spotmaniac-dog-pedigree'); ?></h2>

            <p>
                <?php
                printf(
                    /* translators: %s: example URL */
                    esc_html__('Append query parameters to your catalog URL (or to any page using %s) to filter results. The catalog form uses these same parameters when submitted.', 'spotmaniac-dog-pedigree'),
                    '<code>[dogped-catalog]</code>'
                );
                ?>
            </p>

            <table class="widefat striped" style="max-width:900px;">
                <thead>
                    <tr>
                        <th style="width:160px;"><?php esc_html_e('Parameter', 'spotmaniac-dog-pedigree'); ?></th>
                        <th><?php esc_html_e('Description', 'spotmaniac-dog-pedigree'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>?s=...</code></td>
                        <td><?php esc_html_e('Search by dog name. Minimum 2 characters.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>?sex=male</code> / <code>?sex=female</code></td>
                        <td><?php esc_html_e('Filter by sex.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>?color=...</code></td>
                        <td><?php esc_html_e('Filter by color (must match a value from your dropdown options).', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>?breeding_status=...</code></td>
                        <td><?php esc_html_e('Filter by breeding status.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>?orderby=...</code></td>
                        <td><?php esc_html_e('Override sort order. Accepts: title, date, modified.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>?order=ASC</code> / <code>?order=DESC</code></td>
                        <td><?php esc_html_e('Sort direction.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>?paged=N</code></td>
                        <td><?php esc_html_e('Page number for pagination.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                </tbody>
            </table>

            <p><strong><?php esc_html_e('Example:', 'spotmaniac-dog-pedigree'); ?></strong></p>
            <pre style="background:#f6f7f7;padding:12px;border-left:4px solid #2271b1;"><?php echo esc_html($catalog_url); ?>?sex=female&amp;color=Black&amp;orderby=date&amp;order=DESC</pre>

            <!-- ============================================================ -->
            <h2><?php esc_html_e('Template overrides', 'spotmaniac-dog-pedigree'); ?></h2>

            <p>
                <?php
                printf(
                    /* translators: %s: theme folder path */
                    esc_html__('To customise any plugin template, copy it from the plugin to %s in your active theme. Your version takes priority and survives plugin updates.', 'spotmaniac-dog-pedigree'),
                    '<code>your-theme/spotmaniac-dog-pedigree/</code>'
                );
                ?>
            </p>

            <table class="widefat striped" style="max-width:900px;">
                <thead>
                    <tr>
                        <th style="width:280px;"><?php esc_html_e('Plugin template', 'spotmaniac-dog-pedigree'); ?></th>
                        <th><?php esc_html_e('Override path', 'spotmaniac-dog-pedigree'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>templates/archive-dog.php</code></td>
                        <td><code>your-theme/spotmaniac-dog-pedigree/archive-dog.php</code></td>
                    </tr>
                    <tr>
                        <td><code>templates/single-dog.php</code></td>
                        <td><code>your-theme/spotmaniac-dog-pedigree/single-dog.php</code></td>
                    </tr>
                    <tr>
                        <td><code>templates/content-dog-card.php</code></td>
                        <td><code>your-theme/spotmaniac-dog-pedigree/content-dog-card.php</code></td>
                    </tr>
                </tbody>
            </table>

            <!-- ============================================================ -->
            <h2><?php esc_html_e('REST API', 'spotmaniac-dog-pedigree'); ?></h2>

            <p>
                <?php
                printf(
                    /* translators: %s: REST namespace base URL */
                    esc_html__('All endpoints are under the namespace %s.', 'spotmaniac-dog-pedigree'),
                    '<code>' . esc_html($rest_root) . '</code>'
                );
                ?>
            </p>

            <table class="widefat striped" style="max-width:900px;">
                <thead>
                    <tr>
                        <th style="width:90px;"><?php esc_html_e('Method', 'spotmaniac-dog-pedigree'); ?></th>
                        <th style="width:280px;"><?php esc_html_e('Endpoint', 'spotmaniac-dog-pedigree'); ?></th>
                        <th style="width:120px;"><?php esc_html_e('Auth', 'spotmaniac-dog-pedigree'); ?></th>
                        <th><?php esc_html_e('Description', 'spotmaniac-dog-pedigree'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>GET</code></td>
                        <td><code>/dog/&lt;id&gt;</code></td>
                        <td><?php esc_html_e('Public', 'spotmaniac-dog-pedigree'); ?></td>
                        <td><?php esc_html_e('Get one dog (public fields only for anonymous users).', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>GET</code></td>
                        <td><code>/search</code></td>
                        <td><?php esc_html_e('Public', 'spotmaniac-dog-pedigree'); ?></td>
                        <td><?php esc_html_e('Search dogs. Same query parameters as the catalog URL filters.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>POST</code></td>
                        <td><code>/dog</code></td>
                        <td><?php esc_html_e('Editor', 'spotmaniac-dog-pedigree'); ?></td>
                        <td><?php esc_html_e('Create a new dog. Requires edit_posts and publish_posts.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>POST</code></td>
                        <td><code>/dog/&lt;id&gt;</code></td>
                        <td><?php esc_html_e('Editor', 'spotmaniac-dog-pedigree'); ?></td>
                        <td><?php esc_html_e('Update a dog. Requires edit_post on the target ID.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>POST</code></td>
                        <td><code>/dog/&lt;id&gt;/owner-update</code></td>
                        <td><?php esc_html_e('Owner', 'spotmaniac-dog-pedigree'); ?></td>
                        <td><?php esc_html_e('Limited update by the dog\'s owner (photo, titles, color, call name, description).', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>DELETE</code></td>
                        <td><code>/dog/&lt;id&gt;</code></td>
                        <td><?php esc_html_e('Editor', 'spotmaniac-dog-pedigree'); ?></td>
                        <td><?php esc_html_e('Send a dog to the trash.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                    <tr>
                        <td><code>GET</code></td>
                        <td><code>/search-parents</code></td>
                        <td><?php esc_html_e('Editor', 'spotmaniac-dog-pedigree'); ?></td>
                        <td><?php esc_html_e('Autocomplete search used by the parent picker on the dog edit screen.', 'spotmaniac-dog-pedigree'); ?></td>
                    </tr>
                </tbody>
            </table>

            <p>
                <?php
                printf(
                    /* translators: %s: example URL */
                    esc_html__('Example: %s', 'spotmaniac-dog-pedigree'),
                    '<code>' . esc_html($rest_root) . 'search?sex=female&amp;per_page=5</code>'
                );
                ?>
            </p>

            <!-- ============================================================ -->
            <h2><?php esc_html_e('Hooks for developers', 'spotmaniac-dog-pedigree'); ?></h2>

            <p>
                <?php esc_html_e('The plugin exposes action and filter hooks so add-ons or your theme can extend behaviour without modifying core files. The most useful are:', 'spotmaniac-dog-pedigree'); ?>
            </p>

            <ul style="list-style:disc;margin-left:20px;">
                <li><code>dogped_loaded</code> - <?php esc_html_e('fired after the plugin finishes booting.', 'spotmaniac-dog-pedigree'); ?></li>
                <li><code>dogped_after_register_post_type</code> - <?php esc_html_e('fired after the dog post type is registered.', 'spotmaniac-dog-pedigree'); ?></li>
                <li><code>dogped_dog_data</code> (filter) - <?php esc_html_e('modify the dog data array returned everywhere.', 'spotmaniac-dog-pedigree'); ?></li>
                <li><code>dogped_single_after_hero</code>, <code>dogped_single_sections</code>, <code>dogped_single_badges</code> - <?php esc_html_e('inject content into the single dog template.', 'spotmaniac-dog-pedigree'); ?></li>
                <li><code>dogped_metaboxes</code>, <code>dogped_save_metaboxes</code> - <?php esc_html_e('add or save custom admin meta boxes.', 'spotmaniac-dog-pedigree'); ?></li>
                <li><code>dogped_register_settings</code>, <code>dogped_section_labels</code> - <?php esc_html_e('add settings sections and reorderable single-page sections.', 'spotmaniac-dog-pedigree'); ?></li>
                <li><code>dogped_register_rest_routes</code> - <?php esc_html_e('register additional REST routes under the same namespace.', 'spotmaniac-dog-pedigree'); ?></li>
            </ul>

            <?php
            /**
             * Pro add-on appends its own help section here.
             */
            do_action('dogped_help_after');
            ?>

            <hr style="margin:30px 0;" />

            <p class="description">
                <?php
                printf(
                    /* translators: %s: external link to Pro */
                    esc_html__('Need pedigree trees, multi-breed taxonomy, CSV import/export, structured health tests, breeder roles with a frontend dashboard, or Schema.org SEO? See %s.', 'spotmaniac-dog-pedigree'),
                    '<a href="https://dogspedigree.lemonsqueezy.com/" target="_blank" rel="noopener">Spotmaniac Dog Pedigree Pro</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }
}
