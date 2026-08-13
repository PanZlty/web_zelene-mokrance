
<?php
/**
 * Plugin Name: Zelené Mokrance – ponuka pozemkov
 * Description: Dynamická tabuľka pozemkov napojená na ACF údaje synchronizované z Google Sheets.
 * Version: 1.1.0
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
                    <th scope="col"><button type="button" class="zm-sort" data-sort="plot" aria-label="Zoradiť podľa čísla pozemku">Pozemok <span aria-hidden="true">↕</span></button></th>
                    <th scope="col"><button type="button" class="zm-sort" data-sort="area" aria-label="Zoradiť podľa rozlohy">Rozloha <span aria-hidden="true">↕</span></button></th>
                    <th scope="col"><button type="button" class="zm-sort" data-sort="price" aria-label="Zoradiť podľa ceny">Cena <span aria-hidden="true">↕</span></button></th>
                    <th scope="col"><button type="button" class="zm-sort" data-sort="status" aria-label="Zoradiť podľa dostupnosti">Dostupnosť <span aria-hidden="true">↕</span></button></th>
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
                    <tr class="zm-plot-row zm-plot-row--<?php echo esc_attr($status); ?>"
                        data-plot="<?php echo esc_attr((int) $plot_id); ?>"
                        data-area="<?php echo esc_attr($area !== null && $area !== '' ? (float) $area : PHP_INT_MAX); ?>"
                        data-price="<?php echo esc_attr($price !== null && $price !== '' ? (float) $price : PHP_INT_MAX); ?>"
                        data-status="<?php echo esc_attr(array_search($status, array('available', 'reserved', 'sold'), true)); ?>">
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
        .zm-plots-table-wrap{width:100%;overflow-x:auto;border:0;border-radius:0;background:transparent;box-shadow:none}
        .zm-plots-table{width:100%;min-width:680px;border-collapse:separate;border-spacing:0 8px;font-family:var(--zm-font-body,Dosis,sans-serif);color:var(--zm-color-ink,#222);font-size:15px}
        .zm-plots-table th,.zm-plots-table td{padding:15px 22px;border:0;text-align:left;vertical-align:middle}
        .zm-plots-table thead th{padding-top:12px;padding-bottom:12px;background:#eef3e6;color:var(--zm-color-header-green,#507d0c);font-size:13px;font-weight:700}
        .zm-plots-table thead th:first-child{border-radius:8px 0 0 8px}
        .zm-plots-table thead th:last-child{border-radius:0 8px 8px 0}
        .zm-sort{display:inline-flex;align-items:center;gap:8px;padding:0;border:0;background:transparent;color:inherit;font:inherit;font-weight:700;cursor:pointer}
        .zm-sort span{font-size:13px;line-height:1;transition:transform .2s ease}
        .zm-sort[aria-sort="ascending"] span{transform:rotate(0deg)}
        .zm-sort[aria-sort="descending"] span{transform:rotate(180deg)}
        .zm-plots-table tbody th{font-weight:600;color:inherit}
        .zm-plots-table th:nth-child(3),.zm-plots-table td:nth-child(3){text-align:right}
        .zm-plots-table th:nth-child(4),.zm-plots-table td:nth-child(4){text-align:center}
        .zm-plots-table tbody tr> :first-child{border-radius:9px 0 0 9px}
        .zm-plots-table tbody tr> :last-child{border-radius:0 9px 9px 0}
        .zm-plot-row--available>th,.zm-plot-row--available>td{background:#fff;color:#171717}
        .zm-plot-row--reserved>th,.zm-plot-row--reserved>td{background:#f6b84a;color:#4f3500}
        .zm-plot-row--sold>th,.zm-plot-row--sold>td{background:#fbefef;color:#8a5555}
        .zm-plots-table tbody tr:hover>th,.zm-plots-table tbody tr:hover>td{filter:brightness(.98)}
        .zm-plot-status{display:block;width:100%;padding:0;background:transparent;border-radius:0;font-size:14px;font-weight:600;text-align:center}
        .zm-plot-status--available{color:var(--zm-color-header-green,#507d0c)}
        .zm-plot-status--reserved,.zm-plot-status--sold{color:inherit}
        @media(max-width:767px){.zm-plots-table th,.zm-plots-table td{padding:13px 14px}}
    </style>
    <script id="zm-ponuka-pozemkov-sort-js">
        document.addEventListener('click',function(event){
            var button=event.target.closest('.zm-sort');
            if(!button){return;}
            var table=button.closest('.zm-plots-table');
            var body=table.querySelector('tbody');
            var key=button.dataset.sort;
            var direction=button.getAttribute('aria-sort')==='ascending'?'descending':'ascending';
            table.querySelectorAll('.zm-sort').forEach(function(item){item.removeAttribute('aria-sort');});
            button.setAttribute('aria-sort',direction);
            var multiplier=direction==='ascending'?1:-1;
            Array.from(body.querySelectorAll('tr')).sort(function(a,b){
                if(key==='price'){
                    var aUnavailable=Number(a.dataset.status)>0;
                    var bUnavailable=Number(b.dataset.status)>0;
                    if(aUnavailable!==bUnavailable){return aUnavailable?1:-1;}
                }
                return (Number(a.dataset[key])-Number(b.dataset[key]))*multiplier;
            }).forEach(function(row){body.appendChild(row);});
        });
    </script>
    <?php
}
add_action('wp_head', 'zm_ponuka_pozemkov_styles', 30);

