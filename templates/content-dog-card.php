<?php

/**
 * Template partial: Dog Card (used in catalog grid and siblings).
 *
 * Override: copy to your-theme/spotmaniac-dog-pedigree/content-dog-card.php
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('ABSPATH')) {
    exit;
}

$dogped_dog_id = $args['dog_id'] ?? get_the_ID();
$dogped_data   = Dogped_Utilities::get_dog_data($dogped_dog_id);
$dogped_name   = $dogped_data['dogped_name'] ?: get_the_title($dogped_dog_id);
$dogped_url    = get_permalink($dogped_dog_id);
?>

<div class="dogped-card" data-sex="<?php echo esc_attr($dogped_data['dogped_sex'] ?? ''); ?>">
    <a href="<?php echo esc_url($dogped_url); ?>" class="dogped-card__link">
        <?php if (! empty($dogped_data['dogped_photo_thumb'])) : ?>
            <div class="dogped-card__image">
                <img src="<?php echo esc_url($dogped_data['dogped_photo_thumb']); ?>"
                     alt="<?php echo esc_attr($dogped_name); ?>"
                     loading="lazy" />
            </div>
        <?php else : ?>
            <div class="dogped-card__image dogped-card__image--placeholder">
                <span class="dashicons dashicons-pets"></span>
            </div>
        <?php endif; ?>

        <div class="dogped-card__info">
            <h3 class="dogped-card__name"><?php echo esc_html($dogped_name); ?></h3>

            <?php if (! empty($dogped_data['dogped_sex'])) : ?>
                <span class="dogped-card__sex dogped-card__sex--<?php echo esc_attr($dogped_data['dogped_sex']); ?>">
                    <?php echo esc_html('male' === $dogped_data['dogped_sex'] ? __('Male', 'spotmaniac-dog-pedigree') : __('Female', 'spotmaniac-dog-pedigree')); ?>
                </span>
            <?php endif; ?>

            <?php if (! empty($dogped_data['dogped_color'])) : ?>
                <span class="dogped-card__color"><?php echo esc_html($dogped_data['dogped_color']); ?></span>
            <?php endif; ?>

            <?php if (! empty($dogped_data['dogped_birth_date'])) : ?>
                <span class="dogped-card__birth"><?php echo esc_html(Dogped_Utilities::format_date($dogped_data['dogped_birth_date'])); ?></span>
            <?php endif; ?>
        </div>
    </a>
</div>
