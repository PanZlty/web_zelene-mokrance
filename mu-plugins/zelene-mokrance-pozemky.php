<?php
/**
 * Plugin Name: Zelené Mokrance – Pozemky
 * Description: CPT Pozemky a ACF polia pripravené pre import cez WP All Import Pro.
 * Version: 1.1.0
 */

defined('ABSPATH') || exit;

const ZM_POZEMKY_POST_TYPE = 'pozemok';
const ZM_POZEMKY_SCHEMA_VERSION = '1.1.0';

add_action('init', function () {
    register_post_type(ZM_POZEMKY_POST_TYPE, array(
        'labels' => array(
            'name' => 'Pozemky',
            'singular_name' => 'Pozemok',
            'add_new_item' => 'Pridať pozemok',
            'edit_item' => 'Upraviť pozemok',
            'new_item' => 'Nový pozemok',
            'view_item' => 'Zobraziť pozemok',
            'search_items' => 'Hľadať pozemky',
            'not_found' => 'Nenašli sa žiadne pozemky',
            'all_items' => 'Všetky pozemky',
            'menu_name' => 'Pozemky',
        ),
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-admin-multisite',
        'supports' => array('title', 'editor', 'thumbnail', 'revisions'),
        'has_archive' => true,
        'rewrite' => array('slug' => 'pozemky', 'with_front' => false),
        'map_meta_cap' => true,
    ));
}, 5);

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(array(
        'key' => 'group_zm_pozemok_obchodne_udaje',
        'title' => 'Údaje pozemku',
        'fields' => array(
            array(
                'key' => 'field_zm_plot_id',
                'label' => 'ID pozemku',
                'name' => 'plot_id',
                'type' => 'text',
                'required' => 1,
                'maxlength' => 2,
                'placeholder' => '01',
                'instructions' => 'Stabilný dvojmiestny kód 01–55. Používa sa ako unique identifier pri importe.',
                'wrapper' => array('width' => '25'),
            ),
            array(
                'key' => 'field_zm_area_m2',
                'label' => 'Rozloha',
                'name' => 'area_m2',
                'type' => 'number',
                'required' => 0,
                'min' => 0,
                'step' => 0.01,
                'append' => 'm²',
                'wrapper' => array('width' => '25'),
            ),
            array(
                'key' => 'field_zm_price',
                'label' => 'Cena',
                'name' => 'price',
                'type' => 'number',
                'required' => 0,
                'min' => 0,
                'step' => 0.01,
                'append' => '€',
                'wrapper' => array('width' => '25'),
                'instructions' => 'Synchronizuje sa z Google Sheets cez WP All Import Pro.',
            ),
            array(
                'key' => 'field_zm_status',
                'label' => 'Status',
                'name' => 'status',
                'type' => 'select',
                'required' => 1,
                'choices' => array(
                    'available' => 'Dostupný',
                    'reserved' => 'Rezervovaný',
                    'sold' => 'Predaný',
                ),
                'default_value' => 'available',
                'return_format' => 'value',
                'allow_null' => 0,
                'ui' => 1,
                'wrapper' => array('width' => '25'),
                'instructions' => 'Synchronizuje sa z Google Sheets cez WP All Import Pro.',
            ),
        ),
        'location' => array(array(array(
            'param' => 'post_type',
            'operator' => '==',
            'value' => ZM_POZEMKY_POST_TYPE,
        ))),
        'position' => 'acf_after_title',
        'style' => 'default',
        'active' => true,
        'show_in_rest' => 1,
    ));
});

function zm_pozemky_seed_posts() {
    if (get_option('zm_pozemky_schema_version') === ZM_POZEMKY_SCHEMA_VERSION) {
        return;
    }

    for ($plot_id = 1; $plot_id <= 55; $plot_id++) {
        $plot_code = sprintf('%02d', $plot_id);
        $existing = get_posts(array(
            'post_type' => ZM_POZEMKY_POST_TYPE,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => 'plot_id',
            'meta_value' => array((string) $plot_id, $plot_code),
            'meta_compare' => 'IN',
            'no_found_rows' => true,
        ));

        if ($existing) {
            $post_id = (int) $existing[0];
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => sprintf('Pozemok %s', $plot_code),
                'post_name' => sprintf('pozemok-%s', $plot_code),
            ));
        } else {
            $post_id = wp_insert_post(array(
                'post_type' => ZM_POZEMKY_POST_TYPE,
                'post_status' => 'publish',
                'post_title' => sprintf('Pozemok %s', $plot_code),
                'post_name' => sprintf('pozemok-%s', $plot_code),
            ), true);
        }

        if (is_wp_error($post_id)) {
            continue;
        }

        update_post_meta($post_id, 'plot_id', $plot_code);
        update_post_meta($post_id, '_plot_id', 'field_zm_plot_id');
        update_post_meta($post_id, 'status', 'available');
        update_post_meta($post_id, '_status', 'field_zm_status');
    }

    update_option('zm_pozemky_schema_version', ZM_POZEMKY_SCHEMA_VERSION, false);
    wp_clear_scheduled_hook('zm_sync_pozemky_from_google_sheets');
    flush_rewrite_rules(false);
}
add_action('admin_init', 'zm_pozemky_seed_posts');
