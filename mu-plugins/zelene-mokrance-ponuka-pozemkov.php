
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
                    <tr class="zm-plot-row zm-plot-row--<?php echo esc_attr($status); ?>">
                        <th scope="row">Pozemok <?php echo esc_html($plot_id); ?></th>
                        <td><?php echo $area !== null && $area !== '' ? esc_html(number_format_i18n((float) $area, 0) . ' m²') : '—'; ?></td>
                        <td><?php
                            if (in_array($status, array('reserved', 'sold'), true)) {
                                echo '–';
                            } else {
                                echo $price !== null && $price !== '' ? esc_html(number_format_i18n((float) $price, 0) . ' €') : 'Na vyžiadanie';
                            }
                        ?></td>
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
        .zm-plots-table-wrap{width:100%;overflow-x:auto;border:0;border-radius:14px;background:#fff;box-shadow:0 12px 34px rgba(34,34,34,.07)}
        .zm-plots-table{width:100%;min-width:680px;border-collapse:separate;border-spacing:0 8px;font-family:var(--zm-font-body,Dosis,sans-serif);color:var(--zm-color-ink,#222)}
        .zm-plots-table th,.zm-plots-table td{padding:16px 18px;border:0;text-align:left;vertical-align:middle}
        .zm-plots-table thead th{background:var(--zm-color-header-green,#507d0c);color:#fff;font-size:16px;font-weight:600;letter-spacing:.02em}
        .zm-plots-table tbody th{font-weight:600;color:inherit}
        .zm-plots-table th:nth-child(3),.zm-plots-table td:nth-child(3){text-align:right}
        .zm-plots-table th:nth-child(4),.zm-plots-table td:nth-child(4){text-align:center}
        .zm-plots-table tbody tr{background:#fff;box-shadow:0 4px 14px rgba(34,34,34,.05)}
        .zm-plots-table tbody tr> :first-child{border-radius:10px 0 0 10px}
        .zm-plots-table tbody tr> :last-child{border-radius:0 10px 10px 0}
        .zm-plots-table tbody tr:hover{filter:brightness(.98)}
        .zm-plot-row--reserved>th,.zm-plot-row--reserved>td{background:#f6b84a;color:#4f3500}
        .zm-plot-row--sold>th,.zm-plot-row--sold>td{background:#e76f6f;color:#fff}
        .zm-plot-row--available>th,.zm-plot-row--available>td{background:#fff}
        .zm-plot-status{display:block;width:100%;padding:0;background:transparent;border-radius:0;font-size:14px;font-weight:600;text-align:center}
        .zm-plot-status--available{color:var(--zm-color-header-green,#507d0c)}
        .zm-plot-status--reserved,.zm-plot-status--sold{color:inherit}
        @media(max-width:767px){.zm-plots-table th,.zm-plots-table td{padding:13px 14px}}
    </style>
    <?php
}
add_action('wp_head', 'zm_ponuka_pozemkov_styles', 30);

