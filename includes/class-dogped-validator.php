<?php

/**
 * Validation logic for dog data submitted via admin or REST API.
 *
 * @package Spotmaniac_Dog_Pedigree
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dogped_Validator
{
    /**
     * @param array $data    Raw field values.
     * @param int   $post_id The dog being saved, when it already exists. Used to
     *                       reject a dog being linked as its own parent; pass 0
     *                       when creating, since there is nothing to compare to.
     */
    public static function validate($data, $post_id = 0)
    {
        $errors = array();
        $clean  = array();

        if (empty($data['dogped_name'])) {
            $errors[] = __('Dog name is required.', 'spotmaniac-dog-pedigree');
        } else {
            $clean['dogped_name'] = sanitize_text_field($data['dogped_name']);
        }

        if (empty($data['dogped_sex']) || ! in_array($data['dogped_sex'], array( 'male', 'female' ), true)) {
            $errors[] = __('Sex must be male or female.', 'spotmaniac-dog-pedigree');
        } else {
            $clean['dogped_sex'] = $data['dogped_sex'];
        }

        $text_fields = array(
            'dogped_call_name', 'dogped_breeder', 'dogped_owner', 'dogped_color', 'dogped_size',
            'dogped_breeding_status', 'dogped_tattoo_number', 'dogped_microchip', 'dogped_club_number',
        );
        foreach ($text_fields as $field) {
            if (isset($data[ $field ])) {
                $clean[ $field ] = sanitize_text_field($data[ $field ]);
            }
        }

        $textarea_fields = array( 'dogped_titles', 'dogped_health', 'dogped_registration_number', 'dogped_description' );
        foreach ($textarea_fields as $field) {
            if (isset($data[ $field ])) {
                $clean[ $field ] = sanitize_textarea_field($data[ $field ]);
            }
        }

        $date_fields = array( 'dogped_birth_date', 'dogped_death_date', 'dogped_registration_date' );
        foreach ($date_fields as $field) {
            if (! empty($data[ $field ])) {
                $parsed = self::parse_date($data[ $field ]);
                if (false === $parsed) {
                    /* translators: %s: field name */
                    $errors[] = sprintf(__('Invalid date format for %s.', 'spotmaniac-dog-pedigree'), $field);
                } else {
                    $clean[ $field ] = $parsed;
                }
            } elseif (isset($data[ $field ])) {
                $clean[ $field ] = '';
            }
        }

        if (! empty($clean['dogped_death_date']) && ! empty($clean['dogped_birth_date'])) {
            if (strtotime($clean['dogped_death_date']) < strtotime($clean['dogped_birth_date'])) {
                $errors[] = __('Death date must be after birth date.', 'spotmaniac-dog-pedigree');
            }
        }

        if (! empty($clean['dogped_registration_date']) && ! empty($clean['dogped_birth_date'])) {
            if (strtotime($clean['dogped_registration_date']) < strtotime($clean['dogped_birth_date'])) {
                $errors[] = __('Registration date must be after birth date.', 'spotmaniac-dog-pedigree');
            }
        }

        $id_fields = array( 'dogped_photo', 'dogped_father_id', 'dogped_mother_id', 'dogped_owner_id' );
        foreach ($id_fields as $field) {
            if (isset($data[ $field ])) {
                $clean[ $field ] = absint($data[ $field ]);
            }
        }

        if (! empty($clean['dogped_father_id'])) {
            $father_sex = get_post_meta($clean['dogped_father_id'], 'dogped_sex', true);
            if ($father_sex && 'male' !== $father_sex) {
                $errors[] = __('The selected father must be male.', 'spotmaniac-dog-pedigree');
            }
        }
        if (! empty($clean['dogped_mother_id'])) {
            $mother_sex = get_post_meta($clean['dogped_mother_id'], 'dogped_sex', true);
            if ($mother_sex && 'female' !== $mother_sex) {
                $errors[] = __('The selected mother must be female.', 'spotmaniac-dog-pedigree');
            }
        }

        // A dog can never be its own parent. The pickers already leave it out of
        // the list and the search, but those are interface measures only: a stale
        // form or a hand-made request can still carry the ID, so refuse it here.
        if ($post_id) {
            $post_id   = (int) $post_id;
            $father_id = ! empty($clean['dogped_father_id']) ? (int) $clean['dogped_father_id'] : 0;
            $mother_id = ! empty($clean['dogped_mother_id']) ? (int) $clean['dogped_mother_id'] : 0;

            if ($father_id === $post_id) {
                $errors[] = __('A dog cannot be its own father.', 'spotmaniac-dog-pedigree');
            } elseif ($father_id && Dogped_Utilities::has_ancestor($father_id, $post_id)) {
                // This dog already sits above the chosen father, so the link would
                // make the two descend from each other.
                $errors[] = __('This dog already appears in the pedigree of the selected father, so linking them would create a loop.', 'spotmaniac-dog-pedigree');
            }

            if ($mother_id === $post_id) {
                $errors[] = __('A dog cannot be its own mother.', 'spotmaniac-dog-pedigree');
            } elseif ($mother_id && Dogped_Utilities::has_ancestor($mother_id, $post_id)) {
                $errors[] = __('This dog already appears in the pedigree of the selected mother, so linking them would create a loop.', 'spotmaniac-dog-pedigree');
            }
        }

        if (! empty($clean['dogped_color'])) {
            $valid_colors = Dogped_Utilities::get_field_options('color');
            if (! empty($valid_colors) && ! in_array($clean['dogped_color'], $valid_colors, true)) {
                $errors[] = __('Invalid color selection.', 'spotmaniac-dog-pedigree');
            }
        }

        if (! empty($errors)) {
            $wp_error = new WP_Error();
            foreach ($errors as $msg) {
                $wp_error->add('validation_error', $msg);
            }
            return $wp_error;
        }

        return $clean;
    }

    public static function parse_date($date)
    {
        $date = trim($date);
        if ('' === $date) {
            return false;
        }

        $formats = array( 'Y-m-d', 'Y/m/d', 'Y.m.d', 'd.m.Y', 'd/m/Y', 'm/d/Y', 'd-m-Y' );
        foreach ($formats as $format) {
            $dt = DateTime::createFromFormat($format, $date);
            if ($dt && $dt->format($format) === $date) {
                return $dt->format('Y-m-d');
            }
        }

        // Deliberately no strtotime() fallback. It answers for input that is not
        // a date at all: a bare "2015" comes back as today, "0000-00-00" as a
        // year before the calendar, "May 2015" as the first of the month. On a
        // pedigree an invented date is worse than none, and worse still because
        // it looks correct. Anything not matching a format above is refused, and
        // the caller reports it.
        return false;
    }

    public static function validate_photo($file)
    {
        $allowed_types = array( 'image/jpeg', 'image/png', 'image/webp' );
        $max_size_mb   = (int) dogped_get_option('photo_max_size', 5);

        if (0 === $max_size_mb) {
            return new WP_Error('photo_disabled', __('Photo uploads are disabled.', 'spotmaniac-dog-pedigree'));
        }

        $file_type = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        if (empty($file_type['type']) || ! in_array($file_type['type'], $allowed_types, true)) {
            return new WP_Error('invalid_type', __('Only JPG, PNG, and WebP images are allowed.', 'spotmaniac-dog-pedigree'));
        }

        if ($max_size_mb > 0 && $file['size'] > $max_size_mb * MB_IN_BYTES) {
            return new WP_Error(
                'file_too_large',
                /* translators: %d: max file size in MB */
                sprintf(__('Photo must be smaller than %d MB.', 'spotmaniac-dog-pedigree'), $max_size_mb)
            );
        }

        return true;
    }

    public static function validate_search($params)
    {
        $clean = array();

        if (! empty($params['s'])) {
            $search = sanitize_text_field($params['s']);
            if (mb_strlen($search) >= 2) {
                $clean['s'] = $search;
            }
        }

        if (! empty($params['sex']) && in_array($params['sex'], array( 'male', 'female' ), true)) {
            $clean['sex'] = $params['sex'];
        }

        if (! empty($params['color'])) {
            $clean['color'] = sanitize_text_field($params['color']);
        }

        if (! empty($params['breeding_status'])) {
            $clean['breeding_status'] = sanitize_text_field($params['breeding_status']);
        }

        $clean['paged']    = max(1, absint($params['paged'] ?? 1));
        $clean['per_page'] = min(50, max(1, absint($params['per_page'] ?? (int) dogped_get_option('catalog_count', 12))));
        $clean['orderby']  = in_array(( $params['orderby'] ?? '' ), array( 'title', 'date', 'modified' ), true)
            ? $params['orderby']
            : dogped_get_option('catalog_orderby', 'title');
        $clean['order']    = in_array(strtoupper($params['order'] ?? ''), array( 'ASC', 'DESC' ), true)
            ? strtoupper($params['order'])
            : 'ASC';

        return $clean;
    }
}
