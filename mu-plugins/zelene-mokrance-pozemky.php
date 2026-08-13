<?php
/**
 * Plugin Name: Zelené Mokrance – inicializácia pozemkov
 * Description: Vytvorí a migruje záznamy Pozemok 01–55. CPT a polia spravuje ACF Pro.
 * Version: 2.0.0
 */

defined('ABSPATH') || exit;

const ZM_POZEMKY_POST_TYPE = 'pozemok';
const ZM_POZEMKY_SCHEMA_VERSION = '2.0.0';

function zm_pozemky_seed_posts() {
    if (!post_type_exists(ZM_POZEMKY_POST_TYPE)) {
        return;
    }

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

        update_field('field_zm_plot_id', $plot_code, $post_id);
        if (!get_field('status', $post_id)) {
            update_field('field_zm_status', 'available', $post_id);
        }
    }

    update_option('zm_pozemky_schema_version', ZM_POZEMKY_SCHEMA_VERSION, false);
    wp_clear_scheduled_hook('zm_sync_pozemky_from_google_sheets');
    flush_rewrite_rules(false);
}
add_action('admin_init', 'zm_pozemky_seed_posts');

