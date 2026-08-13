<?php
/**
 * Plugin Name: Zelené Mokrance – pozemky a synchronizácia
 * Description: Inicializuje pozemky 01–55 a synchronizuje ich obchodné údaje z Google Sheets do ACF Pro.
 * Version: 2.1.0
 */

defined('ABSPATH') || exit;

const ZM_POZEMKY_POST_TYPE = 'pozemok';
const ZM_POZEMKY_SCHEMA_VERSION = '2.0.0';
const ZM_POZEMKY_CSV_URL = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSUz-pCqMAo0Cd1ws231XGmfssIbaaNev4kfkW3iiDara83HPe_0coyiI9tu29lMCzSnroDhi-0MWnI/pub?output=csv';

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
    flush_rewrite_rules(false);
}
add_action('admin_init', 'zm_pozemky_seed_posts');

function zm_pozemky_csv_url() {
    return (string) apply_filters('zm_pozemky_csv_url', ZM_POZEMKY_CSV_URL);
}

function zm_pozemky_normalize_text($value) {
    $value = remove_accents(trim((string) $value));
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '_', $value);
    return trim((string) $value, '_');
}

function zm_pozemky_parse_number($value) {
    $value = str_replace(array("\xc2\xa0", ' '), '', trim((string) $value));
    if ($value === '') {
        return null;
    }

    $value = preg_replace('/[^0-9,.-]/u', '', $value);
    if (substr_count($value, ',') === 1 && substr_count($value, '.') === 0) {
        $value = str_replace(',', '.', $value);
    } else {
        $value = str_replace(',', '', $value);
    }

    return is_numeric($value) ? (float) $value : null;
}

function zm_pozemky_status_value($value) {
    $statuses = array(
        'dostupny' => 'available',
        'available' => 'available',
        'rezervovany' => 'reserved',
        'reserved' => 'reserved',
        'predany' => 'sold',
        'sold' => 'sold',
    );
    $key = zm_pozemky_normalize_text($value);
    return isset($statuses[$key]) ? $statuses[$key] : null;
}

function zm_pozemky_store_sync_result($status, $message, $updated = 0) {
    $result = array(
        'status' => $status,
        'message' => $message,
        'updated' => (int) $updated,
        'time' => current_time('mysql'),
    );
    update_option('zm_pozemky_last_sync', $result, false);
    return $result;
}

function zm_pozemky_sync_from_google_sheets() {
    if (!function_exists('update_field') || !post_type_exists(ZM_POZEMKY_POST_TYPE)) {
        return zm_pozemky_store_sync_result('error', 'ACF Pro alebo typ obsahu Pozemky nie je dostupný.');
    }

    if (get_transient('zm_pozemky_sync_lock')) {
        return zm_pozemky_store_sync_result('error', 'Synchronizácia už prebieha.');
    }
    set_transient('zm_pozemky_sync_lock', 1, 5 * MINUTE_IN_SECONDS);

    $response = wp_safe_remote_get(zm_pozemky_csv_url(), array('timeout' => 25, 'redirection' => 3));
    if (is_wp_error($response)) {
        delete_transient('zm_pozemky_sync_lock');
        return zm_pozemky_store_sync_result('error', 'Google Sheets sa nepodarilo načítať: ' . $response->get_error_message());
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    if ($http_code !== 200 || trim($body) === '') {
        delete_transient('zm_pozemky_sync_lock');
        return zm_pozemky_store_sync_result('error', sprintf('Google Sheets vrátil HTTP %d. Tabuľka musí byť dostupná na čítanie cez odkaz.', $http_code));
    }

    $stream = fopen('php://temp', 'r+');
    fwrite($stream, $body);
    rewind($stream);
    $header = fgetcsv($stream);
    if (!$header) {
        fclose($stream);
        delete_transient('zm_pozemky_sync_lock');
        return zm_pozemky_store_sync_result('error', 'CSV neobsahuje hlavičku.');
    }

    $header = array_map('zm_pozemky_normalize_text', $header);
    $aliases = array(
        'plot_id' => array('id_pozemku', 'plot_id'),
        'price' => array('cena', 'price'),
        'area_m2' => array('rozloha_m2', 'rozloha', 'area_m2'),
        'status' => array('stav', 'status'),
    );
    $columns = array();
    foreach ($aliases as $field => $names) {
        foreach ($names as $name) {
            $index = array_search($name, $header, true);
            if ($index !== false) {
                $columns[$field] = $index;
                break;
            }
        }
    }
    if (count($columns) !== 4) {
        fclose($stream);
        delete_transient('zm_pozemky_sync_lock');
        return zm_pozemky_store_sync_result('error', 'Chýbajú povinné stĺpce: ID pozemku, Cena, Rozloha (m²), Stav.');
    }

    $rows = array();
    while (($row = fgetcsv($stream)) !== false) {
        $plot_code = sprintf('%02d', (int) ($row[$columns['plot_id']] ?? 0));
        if ($plot_code < '01' || $plot_code > '55' || isset($rows[$plot_code])) {
            continue;
        }
        $status = zm_pozemky_status_value($row[$columns['status']] ?? '');
        if ($status === null) {
            continue;
        }
        $rows[$plot_code] = array(
            'price' => zm_pozemky_parse_number($row[$columns['price']] ?? ''),
            'area_m2' => zm_pozemky_parse_number($row[$columns['area_m2']] ?? ''),
            'status' => $status,
        );
    }
    fclose($stream);

    if (count($rows) !== 55) {
        delete_transient('zm_pozemky_sync_lock');
        return zm_pozemky_store_sync_result('error', sprintf('Validácia zlyhala: očakáva sa 55 jedinečných pozemkov, načítaných bolo %d. Nič sa nezmenilo.', count($rows)));
    }

    $updated = 0;
    foreach ($rows as $plot_code => $data) {
        $posts = get_posts(array(
            'post_type' => ZM_POZEMKY_POST_TYPE,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => 'plot_id',
            'meta_value' => $plot_code,
            'no_found_rows' => true,
        ));
        if (!$posts) {
            continue;
        }
        $post_id = (int) $posts[0];
        update_field('field_zm_plot_id', $plot_code, $post_id);
        update_field('field_zm_status', $data['status'], $post_id);
        if ($data['price'] !== null) {
            update_field('field_zm_price', $data['price'], $post_id);
        }
        if ($data['area_m2'] !== null) {
            update_field('field_zm_area_m2', $data['area_m2'], $post_id);
        }
        $updated++;
    }

    delete_transient('zm_pozemky_sync_lock');
    return zm_pozemky_store_sync_result('success', sprintf('Synchronizovaných pozemkov: %d.', $updated), $updated);
}
add_action('zm_sync_pozemky_from_google_sheets', 'zm_pozemky_sync_from_google_sheets');

function zm_pozemky_cron_interval($schedules) {
    $schedules['zm_every_fifteen_minutes'] = array(
        'interval' => 15 * MINUTE_IN_SECONDS,
        'display' => 'Každých 15 minút',
    );
    return $schedules;
}
add_filter('cron_schedules', 'zm_pozemky_cron_interval');

function zm_pozemky_schedule_sync() {
    if (!wp_next_scheduled('zm_sync_pozemky_from_google_sheets')) {
        wp_schedule_event(time() + 60, 'zm_every_fifteen_minutes', 'zm_sync_pozemky_from_google_sheets');
    }
}
add_action('init', 'zm_pozemky_schedule_sync');

function zm_pozemky_register_tools_page() {
    add_management_page('Synchronizácia pozemkov', 'Synchronizácia pozemkov', 'manage_options', 'zm-pozemky-sync', 'zm_pozemky_render_tools_page');
}
add_action('admin_menu', 'zm_pozemky_register_tools_page');

function zm_pozemky_render_tools_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    if (isset($_POST['zm_sync_now']) && check_admin_referer('zm_pozemky_sync_now')) {
        zm_pozemky_sync_from_google_sheets();
    }
    $last = get_option('zm_pozemky_last_sync', array());
    ?>
    <div class="wrap">
        <h1>Synchronizácia pozemkov</h1>
        <p><strong>Zdroj:</strong> Google Sheets – hárok Pozemky</p>
        <p><strong>Mapovanie:</strong> ID pozemku → plot_id, Cena → price, Rozloha (m²) → area_m2, Stav → status.</p>
        <?php if ($last) : ?>
            <div class="notice <?php echo $last['status'] === 'success' ? 'notice-success' : 'notice-error'; ?> inline"><p>
                <?php echo esc_html(sprintf('%s — %s', $last['time'], $last['message'])); ?>
            </p></div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('zm_pozemky_sync_now'); ?>
            <?php submit_button('Synchronizovať teraz', 'primary', 'zm_sync_now'); ?>
        </form>
    </div>
    <?php
}

