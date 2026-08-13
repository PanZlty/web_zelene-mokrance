<?php
/**
 * Plugin Name: Zelené Mokrance – Pozemky
 * Description: CPT Pozemky, ACF polia a synchronizácia obchodných údajov z Google Sheets CSV.
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

const ZM_POZEMKY_POST_TYPE = 'pozemok';
const ZM_POZEMKY_SYNC_HOOK = 'zm_sync_pozemky_from_google_sheets';
const ZM_POZEMKY_SCHEMA_VERSION = '1.0.0';

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
                'type' => 'number',
                'required' => 1,
                'min' => 1,
                'max' => 55,
                'step' => 1,
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
                'instructions' => 'Synchronizuje sa z Google Sheets.',
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
                'instructions' => 'Synchronizuje sa z Google Sheets.',
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
        $existing = get_posts(array(
            'post_type' => ZM_POZEMKY_POST_TYPE,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => 'plot_id',
            'meta_value' => (string) $plot_id,
            'no_found_rows' => true,
        ));

        if ($existing) {
            continue;
        }

        $post_id = wp_insert_post(array(
            'post_type' => ZM_POZEMKY_POST_TYPE,
            'post_status' => 'publish',
            'post_title' => sprintf('Pozemok %d', $plot_id),
            'post_name' => sprintf('pozemok-%d', $plot_id),
        ), true);

        if (is_wp_error($post_id)) {
            continue;
        }

        update_post_meta($post_id, 'plot_id', $plot_id);
        update_post_meta($post_id, '_plot_id', 'field_zm_plot_id');
        update_post_meta($post_id, 'status', 'available');
        update_post_meta($post_id, '_status', 'field_zm_status');
    }

    update_option('zm_pozemky_schema_version', ZM_POZEMKY_SCHEMA_VERSION, false);
    flush_rewrite_rules(false);
}
add_action('admin_init', 'zm_pozemky_seed_posts');

function zm_pozemky_sheet_csv_url() {
    $url = defined('ZM_POZEMKY_GOOGLE_SHEET_CSV_URL') ? ZM_POZEMKY_GOOGLE_SHEET_CSV_URL : '';
    $url = apply_filters('zm_pozemky_google_sheet_csv_url', $url);
    return is_string($url) ? esc_url_raw(trim($url)) : '';
}

function zm_pozemky_normalize_status($value) {
    $value = sanitize_title(remove_accents(trim((string) $value)));
    $aliases = array(
        'available' => array('available', 'dostupny', 'dostupne', 'volny', 'volne', 'na-predaj'),
        'reserved' => array('reserved', 'rezervovany', 'rezervovane'),
        'sold' => array('sold', 'predany', 'predane'),
    );

    foreach ($aliases as $status => $values) {
        if (in_array($value, $values, true)) {
            return $status;
        }
    }

    return '';
}

function zm_pozemky_parse_decimal($value) {
    $value = preg_replace('/[^0-9,.-]/', '', (string) $value);
    $value = str_replace(',', '.', $value);
    return is_numeric($value) ? (float) $value : null;
}

function zm_pozemky_sync_from_google_sheets() {
    $url = zm_pozemky_sheet_csv_url();
    if (!$url || strtolower((string) wp_parse_url($url, PHP_URL_SCHEME)) !== 'https') {
        update_option('zm_pozemky_last_sync', array('time' => current_time('mysql'), 'success' => false, 'message' => 'Google Sheets CSV URL nie je nastavená.'), false);
        return new WP_Error('zm_missing_sheet_url', 'Google Sheets CSV URL nie je nastavená.');
    }

    $response = wp_safe_remote_get($url, array('timeout' => 20, 'redirection' => 3));
    if (is_wp_error($response)) {
        return $response;
    }
    if (wp_remote_retrieve_response_code($response) !== 200) {
        return new WP_Error('zm_sheet_http_error', 'Google Sheets vrátil HTTP ' . wp_remote_retrieve_response_code($response) . '.');
    }

    $body = wp_remote_retrieve_body($response);
    $stream = fopen('php://temp', 'r+');
    fwrite($stream, $body);
    rewind($stream);
    $headers = fgetcsv($stream);
    if (!$headers) {
        fclose($stream);
        return new WP_Error('zm_sheet_empty', 'CSV neobsahuje hlavičku.');
    }
    $headers = array_map(function ($header) {
        return sanitize_key(trim((string) $header));
    }, $headers);

    $updated = 0;
    $skipped = 0;
    while (($values = fgetcsv($stream)) !== false) {
        $values = array_pad($values, count($headers), '');
        $row = array_combine($headers, array_slice($values, 0, count($headers)));
        $plot_id = isset($row['plot_id']) ? absint($row['plot_id']) : (isset($row['id']) ? absint($row['id']) : 0);
        if ($plot_id < 1 || $plot_id > 55) {
            $skipped++;
            continue;
        }
        $posts = get_posts(array(
            'post_type' => ZM_POZEMKY_POST_TYPE,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => 'plot_id',
            'meta_value' => (string) $plot_id,
            'no_found_rows' => true,
        ));
        if (!$posts) {
            $skipped++;
            continue;
        }
        $post_id = (int) $posts[0];
        if (array_key_exists('price', $row) && trim((string) $row['price']) !== '') {
            $price = zm_pozemky_parse_decimal($row['price']);
            if ($price !== null && $price >= 0) {
                update_field('field_zm_price', $price, $post_id);
            }
        }
        if (array_key_exists('status', $row)) {
            $status = zm_pozemky_normalize_status($row['status']);
            if ($status) {
                update_field('field_zm_status', $status, $post_id);
            }
        }
        if (array_key_exists('area_m2', $row) && trim((string) $row['area_m2']) !== '') {
            $area = zm_pozemky_parse_decimal($row['area_m2']);
            if ($area !== null && $area >= 0) {
                update_field('field_zm_area_m2', $area, $post_id);
            }
        }
        $updated++;
    }
    fclose($stream);

    $result = array('time' => current_time('mysql'), 'success' => true, 'updated' => $updated, 'skipped' => $skipped);
    update_option('zm_pozemky_last_sync', $result, false);
    return $result;
}
add_action(ZM_POZEMKY_SYNC_HOOK, 'zm_pozemky_sync_from_google_sheets');

add_filter('cron_schedules', function ($schedules) {
    $schedules['zm_every_fifteen_minutes'] = array('interval' => 15 * MINUTE_IN_SECONDS, 'display' => 'Každých 15 minút');
    return $schedules;
});

add_action('init', function () {
    if (!wp_next_scheduled(ZM_POZEMKY_SYNC_HOOK)) {
        wp_schedule_event(time() + 5 * MINUTE_IN_SECONDS, 'zm_every_fifteen_minutes', ZM_POZEMKY_SYNC_HOOK);
    }
}, 20);

