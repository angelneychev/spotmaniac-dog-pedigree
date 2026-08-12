<?php

/**
 * Template: Single Dog page.
 *
 * Override: copy to your-theme/spotmaniac-dog-pedigree/single-dog.php
 *
 * Pro add-on hooks injected: dogped_single_after_hero, dogped_single_after_details,
 * dogped_single_sections (for full custom sections like the pedigree tree).
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

$dogped_dog_id = get_the_ID();
$dogped_data   = Dogped_Utilities::get_dog_data($dogped_dog_id);

$dogped_show = array(
    'registration_number' => 'yes' === get_option('dogped_show_registration_number', 'yes'),
    'registration_date'   => 'yes' === get_option('dogped_show_registration_date', 'yes'),
    'tattoo'              => 'yes' === get_option('dogped_show_tattoo', 'yes'),
    'microchip'           => 'yes' === get_option('dogped_show_microchip', 'yes'),
    'club_number'         => 'yes' === get_option('dogped_show_club_number', 'yes'),
    'breeder'             => 'yes' === get_option('dogped_show_breeder', 'yes'),
    'health'              => 'yes' === get_option('dogped_show_health', 'yes'),
    'birth_date'          => 'yes' === get_option('dogped_show_birth_date', 'yes'),
    'death_date'          => 'yes' === get_option('dogped_show_death_date', 'yes'),
    'titles'              => 'yes' === get_option('dogped_show_titles', 'yes'),
    'description'         => 'yes' === get_option('dogped_show_description', 'yes'),
);

$dogped_has_photo = ! empty($dogped_data['dogped_photo_url']);
$dogped_sex_label = ! empty($dogped_data['dogped_sex'])
    ? ('male' === $dogped_data['dogped_sex'] ? __('Male', 'spotmaniac-dog-pedigree') : __('Female', 'spotmaniac-dog-pedigree'))
    : '';
$dogped_sex_class = ! empty($dogped_data['dogped_sex']) ? 'dogped-sex--' . esc_attr($dogped_data['dogped_sex']) : '';

$dogped_has_id = ($dogped_show['registration_number'] && ! empty($dogped_data['dogped_registration_number']))
    || ($dogped_show['registration_date'] && ! empty($dogped_data['dogped_registration_date']))
    || ($dogped_show['tattoo'] && ! empty($dogped_data['dogped_tattoo_number']))
    || ($dogped_show['microchip'] && ! empty($dogped_data['dogped_microchip']))
    || ($dogped_show['club_number'] && ! empty($dogped_data['dogped_club_number']));

$dogped_siblings      = Dogped_Utilities::get_siblings($dogped_dog_id);
$dogped_custom_values = Dogped_Utilities::get_custom_field_values($dogped_dog_id);
?>

<div class="dogped-single-wrap">
    <article class="dogped-single" id="dog-<?php echo esc_attr($dogped_dog_id); ?>">

        <div class="dogped-single__hero <?php echo $dogped_has_photo ? '' : 'dogped-single__hero--no-photo'; ?>">

            <?php if ($dogped_has_photo) : ?>
                <div class="dogped-single__hero-photo">
                    <img src="<?php echo esc_url($dogped_data['dogped_photo_url']); ?>"
                         alt="<?php echo esc_attr($dogped_data['dogped_name']); ?>"
                         class="dogped-single__image" />
                </div>
            <?php endif; ?>

            <div class="dogped-single__hero-info">
                <h1 class="dogped-single__title"><?php echo esc_html($dogped_data['dogped_name'] ?: get_the_title()); ?></h1>

                <?php if ($dogped_data['dogped_call_name'] ?? '') : ?>
                    <p class="dogped-single__callname">&ldquo;<?php echo esc_html($dogped_data['dogped_call_name']); ?>&rdquo;</p>
                <?php endif; ?>

                <div class="dogped-single__badges">
                    <?php if ($dogped_sex_label) : ?>
                        <span class="dogped-badge <?php echo esc_attr($dogped_sex_class); ?>"><?php echo esc_html($dogped_sex_label); ?></span>
                    <?php endif; ?>
                    <?php
                    /**
                     * Pro add-on injects breed/variety badges here.
                     */
                    do_action('dogped_single_badges', $dogped_dog_id, $dogped_data);
                    ?>
                    <?php if (! empty($dogped_data['dogped_breeding_status'])) : ?>
                        <span class="dogped-badge dogped-badge--status"><?php echo esc_html($dogped_data['dogped_breeding_status']); ?></span>
                    <?php endif; ?>
                </div>

                <dl class="dogped-single__quick-facts">
                    <?php if (! empty($dogped_data['dogped_color'])) : ?>
                        <div class="dogped-fact">
                            <dt><?php esc_html_e('Color', 'spotmaniac-dog-pedigree'); ?></dt>
                            <dd><?php echo esc_html($dogped_data['dogped_color']); ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (! empty($dogped_data['dogped_size'])) : ?>
                        <div class="dogped-fact">
                            <dt><?php esc_html_e('Size', 'spotmaniac-dog-pedigree'); ?></dt>
                            <dd><?php echo esc_html($dogped_data['dogped_size']); ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($dogped_show['birth_date'] && ! empty($dogped_data['dogped_birth_date'])) : ?>
                        <div class="dogped-fact">
                            <dt><?php esc_html_e('Born', 'spotmaniac-dog-pedigree'); ?></dt>
                            <dd><?php echo esc_html(Dogped_Utilities::format_date($dogped_data['dogped_birth_date'])); ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($dogped_show['death_date'] && ! empty($dogped_data['dogped_death_date'])) : ?>
                        <div class="dogped-fact">
                            <dt><?php esc_html_e('Died', 'spotmaniac-dog-pedigree'); ?></dt>
                            <dd><?php echo esc_html(Dogped_Utilities::format_date($dogped_data['dogped_death_date'])); ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($dogped_show['breeder'] && ! empty($dogped_data['dogped_breeder'])) : ?>
                        <div class="dogped-fact">
                            <dt><?php esc_html_e('Breeder', 'spotmaniac-dog-pedigree'); ?></dt>
                            <dd><?php echo esc_html($dogped_data['dogped_breeder']); ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($dogped_show['titles'] && ! empty($dogped_data['dogped_titles'])) : ?>
                        <div class="dogped-fact dogped-fact--wide">
                            <dt><?php esc_html_e('Titles', 'spotmaniac-dog-pedigree'); ?></dt>
                            <dd><?php echo esc_html($dogped_data['dogped_titles']); ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <?php
        /**
         * Pro add-on hook: render content immediately under the hero (e.g. SEO breadcrumbs).
         */
        do_action('dogped_single_after_hero', $dogped_dog_id, $dogped_data);
        ?>

        <?php if ($dogped_show['description'] && ! empty($dogped_data['dogped_description'])) : ?>
            <section class="dogped-single__section dogped-single__description">
                <?php echo wp_kses_post(wpautop($dogped_data['dogped_description'])); ?>
            </section>
        <?php endif; ?>

        <?php if ($dogped_has_id || ($dogped_show['health'] && ! empty($dogped_data['dogped_health']))) : ?>
            <div class="dogped-single__details-grid">

                <?php if ($dogped_has_id) : ?>
                    <section class="dogped-single__section dogped-single__identification">
                        <h2><?php esc_html_e('Identification', 'spotmaniac-dog-pedigree'); ?></h2>
                        <table class="dogped-info-table">
                            <?php if ($dogped_show['registration_number'] && ! empty($dogped_data['dogped_registration_number'])) : ?>
                                <tr>
                                    <th><?php esc_html_e('Reg. Number', 'spotmaniac-dog-pedigree'); ?></th>
                                    <td><?php echo nl2br(esc_html($dogped_data['dogped_registration_number'])); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($dogped_show['registration_date'] && ! empty($dogped_data['dogped_registration_date'])) : ?>
                                <tr>
                                    <th><?php esc_html_e('Reg. Date', 'spotmaniac-dog-pedigree'); ?></th>
                                    <td><?php echo esc_html(Dogped_Utilities::format_date($dogped_data['dogped_registration_date'])); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($dogped_show['tattoo'] && ! empty($dogped_data['dogped_tattoo_number'])) : ?>
                                <tr>
                                    <th><?php esc_html_e('Tattoo', 'spotmaniac-dog-pedigree'); ?></th>
                                    <td><?php echo esc_html($dogped_data['dogped_tattoo_number']); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($dogped_show['microchip'] && ! empty($dogped_data['dogped_microchip'])) : ?>
                                <tr>
                                    <th><?php esc_html_e('Microchip', 'spotmaniac-dog-pedigree'); ?></th>
                                    <td><?php echo esc_html($dogped_data['dogped_microchip']); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($dogped_show['club_number'] && ! empty($dogped_data['dogped_club_number'])) : ?>
                                <tr>
                                    <th><?php esc_html_e('Club Number', 'spotmaniac-dog-pedigree'); ?></th>
                                    <td><?php echo esc_html($dogped_data['dogped_club_number']); ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </section>
                <?php endif; ?>

                <?php if ($dogped_show['health'] && ! empty($dogped_data['dogped_health'])) : ?>
                    <section class="dogped-single__section dogped-single__health">
                        <h2><?php esc_html_e('Health', 'spotmaniac-dog-pedigree'); ?></h2>
                        <div class="dogped-health-content">
                            <?php echo nl2br(esc_html($dogped_data['dogped_health'])); ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php
                $dogped_has_custom = false;
                foreach ($dogped_custom_values as $dogped_cf) {
                    if (! empty($dogped_cf['value'])) {
                        $dogped_has_custom = true;
                        break;
                    }
                }
                if ($dogped_has_custom) :
                    ?>
                    <section class="dogped-single__section dogped-single__custom-fields">
                        <h2><?php esc_html_e('Additional Info', 'spotmaniac-dog-pedigree'); ?></h2>
                        <table class="dogped-info-table">
                            <?php foreach ($dogped_custom_values as $dogped_cf) : ?>
                                <?php if (! empty($dogped_cf['value'])) : ?>
                                    <tr>
                                        <th><?php echo esc_html($dogped_cf['label']); ?></th>
                                        <td><?php echo esc_html($dogped_cf['value']); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </table>
                    </section>
                <?php endif; ?>

            </div>
        <?php endif; ?>

        <?php
        /**
         * Pro add-on hook: render extra full-width sections (e.g. pedigree tree, health tests table).
         */
        do_action('dogped_single_sections', $dogped_dog_id, $dogped_data);
        ?>

        <?php if (! empty($dogped_siblings)) : ?>
            <section class="dogped-single__section dogped-single__siblings">
                <h2><?php esc_html_e('Litter Mates', 'spotmaniac-dog-pedigree'); ?></h2>
                <div class="dogped-grid dogped-grid--siblings">
                    <?php foreach ($dogped_siblings as $dogped_sibling) : ?>
                        <?php dogped_get_template('content-dog-card.php', array( 'dog_id' => $dogped_sibling->ID )); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

    </article>
</div>

<?php
get_footer();
