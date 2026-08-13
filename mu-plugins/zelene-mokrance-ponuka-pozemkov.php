
<?php
/**
 * Plugin Name: Zelené Mokrance – ponuka pozemkov
 * Description: Dynamická tabuľka pozemkov napojená na ACF údaje synchronizované z Google Sheets.
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

function zm_render_ponuka_pozemkov($atts) {
    $atts = shortcode_atts(array('limit' => 19), $atts, 'zm_pozemky_table');
    $limit = max(1, min(55, (int) $atts['limit']));
    $posts = get_posts(array(
        'post_type' => 'pozemok',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'meta_key' => 'plot_id',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'no_found_rows' => true,
    ));

    if (!$posts) {
        return '<p class="zm-plots-empty">Ponuka pozemkov sa pripravuje.</p>';
    }

    $labels = array(
        'available' => 'Dostupný',
        'reserved' => 'Rezervovaný',
        'sold' => 'Predaný',
    );

    ob_start();
    ?>
    <div class="zm-plots-table-wrap" role="region" aria-label="Ponuka pozemkov" tabindex="0">
        <table class="zm-plots-table">
            <thead>
                <tr>
                    <th scope="col">Číslo pozemku</th>
                    <th scope="col">Rozloha</th>
                    <th scope="col">Cena</th>
                    <th scope="col">Stav</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post) :
                    $plot_id = (string) get_field('plot_id', $post->ID);
                    $area = get_field('area_m2', $post->ID);
                    $price = get_field('price', $post->ID);
                    $status = (string) get_field('status', $post->ID);
                    $status = isset($labels[$status]) ? $status : 'available';
                    ?>
                    <tr>
                        <th scope="row">Pozemok <?php echo esc_html($plot_id); ?></th>
                        <td><?php echo $area !== null && $area !== '' ? esc_html(number_format_i18n((float) $area, 0) . ' m²') : '—'; ?></td>
                        <td><?php echo $price !== null && $price !== '' ? esc_html(number_format_i18n((float) $price, 0) . ' €') : 'Na vyžiadanie'; ?></td>
                        <td><span class="zm-plot-status zm-plot-status--<?php echo esc_attr($status); ?>"><?php echo esc_html($labels[$status]); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return (string) ob_get_clean();
}
add_shortcode('zm_pozemky_table', 'zm_render_ponuka_pozemkov');

function zm_ponuka_pozemkov_styles() {
    ?>
    <style id="zm-ponuka-pozemkov-css">
        .zm-plots-table-wrap{width:100%;overflow-x:auto;border:1px solid #dce5cf;border-radius:14px;background:#fff;box-shadow:0 12px 34px rgba(34,34,34,.07)}
        .zm-plots-table{width:100%;min-width:680px;border-collapse:collapse;font-family:var(--zm-font-body,Dosis,sans-serif);color:var(--zm-color-ink,#222)}
        .zm-plots-table th,.zm-plots-table td{padding:16px 18px;border-right:1px solid #e5eadf;border-bottom:1px solid #e5eadf;text-align:left;vertical-align:middle}
        .zm-plots-table th:last-child,.zm-plots-table td:last-child{border-right:0}
        .zm-plots-table tbody tr:last-child th,.zm-plots-table tbody tr:last-child td{border-bottom:0}
        .zm-plots-table thead th{background:var(--zm-color-header-green,#507d0c);color:#fff;font-size:16px;font-weight:600;letter-spacing:.02em}
        .zm-plots-table tbody th{font-weight:600;color:var(--zm-color-header-green,#507d0c)}
        .zm-plots-table tbody tr:nth-child(even){background:#f8faf5}
        .zm-plots-table tbody tr:hover{background:#f1f6e9}
        .zm-plot-status{display:inline-flex;min-width:118px;justify-content:center;padding:8px 12px;border-radius:999px;font-size:14px;font-weight:600}
        .zm-plot-status--available{background:#e5f3d1;color:#365c09}
        .zm-plot-status--reserved{background:#fff0bf;color:#765700}
        .zm-plot-status--sold{background:#f8d7d7;color:#8a2020}
        @media(max-width:767px){.zm-plots-table th,.zm-plots-table td{padding:13px 14px}}
    </style>
    <?php
}
add_action('wp_head', 'zm_ponuka_pozemkov_styles', 30);


