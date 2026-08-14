<?php
/**
 * Plugin Name: Zelené Mokrance – Image Map pozemkov
 * Description: Dynamické tooltipy, stavové farby a formulár obhliadky pre Image Map Pro.
 * Version: 1.1.0
 */

defined('ABSPATH') || exit;

function zm_imp_plot($code) {
    $code = sprintf('%02d', absint($code));
    if ($code < '01' || $code > '19') {
        return null;
    }

    $ids = get_posts(array(
        'post_type' => 'pozemok',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => 'plot_id',
        'meta_value' => array($code, (string) absint($code)),
        'meta_compare' => 'IN',
        'no_found_rows' => true,
    ));
    if (!$ids) {
        return null;
    }

    $post_id = (int) $ids[0];
    $field = static function ($name) use ($post_id) {
        return function_exists('get_field') ? get_field($name, $post_id) : get_post_meta($post_id, $name, true);
    };
    $status = sanitize_key((string) $field('status'));
    if (!in_array($status, array('available', 'reserved', 'sold'), true)) {
        $status = 'available';
    }

    $labels = array('available' => 'Dostupný', 'reserved' => 'Rezervovaný', 'sold' => 'Predaný');
    $price = (float) $field('price');
    $area = (float) $field('area_m2');

    return array(
        'code' => $code,
        'area' => $area,
        'areaLabel' => $area > 0 ? number_format_i18n($area, 0) . ' m²' : '–',
        'price' => $status === 'available' && $price > 0 ? $price : null,
        'priceLabel' => $status === 'available' && $price > 0 ? number_format_i18n($price, 0) . ' €' : '–',
        'status' => $status,
        'statusLabel' => $labels[$status],
    );
}

function zm_imp_all_plots() {
    $plots = array();
    for ($i = 1; $i <= 19; $i++) {
        $plot = zm_imp_plot($i);
        if ($plot) {
            $plots[$plot['code']] = $plot;
        }
    }
    return $plots;
}

add_shortcode('zm_pozemok_tooltip', function ($atts) {
    $atts = shortcode_atts(array('id' => ''), $atts, 'zm_pozemok_tooltip');
    $plot = zm_imp_plot($atts['id']);
    if (!$plot) {
        return '';
    }
    $cta = $plot['status'] === 'available'
        ? sprintf('<button type="button" class="zm-map-tooltip__cta" data-zm-reserve="%s">Rezervovať obhliadku</button>', esc_attr($plot['code']))
        : '';
    return sprintf(
        '<article class="zm-map-tooltip zm-map-tooltip--%1$s" data-zm-plot="%2$s"><div class="zm-map-tooltip__top"><h3>Pozemok %2$s</h3><span>%3$s</span></div><dl><div><dt>Rozloha</dt><dd>%4$s</dd></div><div><dt>Cena</dt><dd>%5$s</dd></div></dl>%6$s</article>',
        esc_attr($plot['status']), esc_attr($plot['code']), esc_html($plot['statusLabel']),
        esc_html($plot['areaLabel']), esc_html($plot['priceLabel']), $cta
    );
});

add_action('wp_ajax_zm_plot_viewing', 'zm_imp_submit_viewing');
add_action('wp_ajax_nopriv_zm_plot_viewing', 'zm_imp_submit_viewing');
function zm_imp_submit_viewing() {
    check_ajax_referer('zm_plot_viewing', 'nonce');
    $code = sprintf('%02d', absint($_POST['plot'] ?? 0));
    $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));
    if ($code < '01' || $code > '19' || $name === '' || !is_email($email) || empty($_POST['gdpr'])) {
        wp_send_json_error(array('message' => 'Skontrolujte povinné údaje a súhlas so spracovaním osobných údajov.'), 422);
    }
    $plot = zm_imp_plot($code);
    if (!$plot || $plot['status'] !== 'available') {
        wp_send_json_error(array('message' => 'Tento pozemok už nie je dostupný na rezerváciu obhliadky.'), 409);
    }
    $body = "Pozemok: {$code}\nMeno: {$name}\nE-mail: {$email}\nTelefón: {$phone}\n\n{$message}";
    $recipient = 'varga@inforeal.sk';
    if (function_exists('get_field')) {
        $broker_email = get_field('zm_broker_email', 'zm_contact_settings');
        $site_email = get_field('zm_site_email', 'zm_contact_settings');
        if (is_string($broker_email) && is_email($broker_email)) {
            $recipient = $broker_email;
        } elseif (is_string($site_email) && is_email($site_email)) {
            $recipient = $site_email;
        }
    }
    $sent = wp_mail($recipient, "Obhliadka pozemku {$code}", $body, array('Reply-To: ' . $name . ' <' . $email . '>'));
    if (!$sent) {
        wp_send_json_error(array('message' => 'Správu sa nepodarilo odoslať. Kontaktujte nás telefonicky.'), 500);
    }
    wp_send_json_success(array('message' => 'Ďakujeme. Ozveme sa vám s návrhom termínu obhliadky.'));
}

add_action('wp_footer', function () {
    if (!is_page(20311)) {
        return;
    }
    $plots = zm_imp_all_plots();
    if (!$plots) {
        return;
    }
    ?>
    <div class="zm-viewing-modal" hidden aria-hidden="true">
      <div class="zm-viewing-modal__backdrop" data-zm-close></div>
      <section class="zm-viewing-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="zm-viewing-title">
        <button type="button" class="zm-viewing-modal__close" data-zm-close aria-label="Zavrieť">×</button>
        <p class="zm-viewing-modal__eyebrow">Osobná obhliadka</p>
        <h2 id="zm-viewing-title">Mám záujem o pozemok <span data-zm-plot-label></span></h2>
        <form class="zm-viewing-form">
          <input type="hidden" name="action" value="zm_plot_viewing"><input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('zm_plot_viewing')); ?>"><input type="hidden" name="plot" value="">
          <label>Meno a priezvisko *<input name="name" autocomplete="name" required></label>
          <label>E-mail *<input type="email" name="email" autocomplete="email" required></label>
          <label>Telefón<input type="tel" name="phone" autocomplete="tel"></label>
          <label>Správa<textarea name="message" rows="4"></textarea></label>
          <label class="zm-viewing-form__gdpr"><input type="checkbox" name="gdpr" value="1" required> Súhlasím so spracovaním osobných údajov na účely vybavenia tejto žiadosti.</label>
          <button type="submit">Odoslať žiadosť</button><p class="zm-viewing-form__response" role="status"></p>
        </form>
      </section>
    </div>
    <style>
    .zm-map-tooltip{--zm-status:#6a8d24;width:100%;max-width:100%;min-width:0;color:var(--zm-color-ink,#222);font-family:"Nunito Sans",sans-serif;container-type:inline-size}
    .zm-map-tooltip,.zm-map-tooltip *{box-sizing:border-box}.zm-map-tooltip--reserved{--zm-status:#c88416}.zm-map-tooltip--sold{--zm-status:#b86161}.zm-map-tooltip__top{display:flex;align-items:center;justify-content:space-between;gap:12px;min-width:0}.zm-map-tooltip h3{margin:0;min-width:0;font-size:23px;line-height:1.2}.zm-map-tooltip__top span{flex:0 0 auto;padding:5px 9px;border-radius:999px;background:color-mix(in srgb,var(--zm-status) 14%,white);color:var(--zm-status);font-size:12px;font-weight:800;text-transform:uppercase}.zm-map-tooltip dl{margin:18px 0;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.zm-map-tooltip dl div{min-width:0;padding:12px;background:#f5f8f0;border-radius:9px}.zm-map-tooltip dt{font-size:12px;color:#66705d}.zm-map-tooltip dd{margin:3px 0 0;font-weight:800;overflow-wrap:anywhere}.zm-map-tooltip__cta,.zm-viewing-form button{width:100%;max-width:100%;min-height:44px;border:0;border-radius:999px;background:var(--zm-color-accent,#9fc74a);color:#22310c;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:10px 18px;line-height:1.2;text-align:center;white-space:normal}
    @container (max-width:280px){.zm-map-tooltip__top{align-items:flex-start;flex-direction:column}.zm-map-tooltip dl{grid-template-columns:1fr}.zm-map-tooltip__cta{border-radius:14px}}
    .zm-viewing-modal[hidden]{display:none}.zm-viewing-modal{position:fixed;inset:0;z-index:10050;display:grid;place-items:center;padding:18px}.zm-viewing-modal__backdrop{position:absolute;inset:0;background:rgba(20,30,12,.72)}.zm-viewing-modal__dialog{position:relative;width:min(560px,100%);max-height:calc(100vh - 36px);overflow:auto;padding:34px;background:#fff;border-radius:18px}.zm-viewing-modal__close{position:absolute;right:16px;top:12px;border:0;background:none;font-size:30px;cursor:pointer}.zm-viewing-modal__eyebrow{margin:0 0 8px;color:var(--zm-color-header-green);font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.1em}.zm-viewing-modal h2{margin:0 36px 22px 0;font-size:30px}.zm-viewing-form{display:grid;grid-template-columns:1fr 1fr;gap:14px}.zm-viewing-form label{display:grid;gap:6px;font-size:14px;font-weight:700}.zm-viewing-form label:nth-of-type(4),.zm-viewing-form__gdpr,.zm-viewing-form button,.zm-viewing-form__response{grid-column:1/-1}.zm-viewing-form input,.zm-viewing-form textarea{width:100%;padding:11px 13px;border:1px solid #ccd3c5;border-radius:8px;font:inherit}.zm-viewing-form__gdpr{display:flex!important;align-items:flex-start;font-weight:500!important}.zm-viewing-form__gdpr input{width:auto;margin-top:3px}.zm-viewing-form__response{margin:0;text-align:center}.zm-imp-status-available{fill:#9fc74a!important;stroke:#6f9228!important;fill-opacity:.28!important}.zm-imp-status-reserved{fill:#d89a35!important;stroke:#b97810!important;fill-opacity:.3!important}.zm-imp-status-sold{fill:#c98282!important;stroke:#ad5d5d!important;fill-opacity:.28!important}@media(max-width:600px){.zm-viewing-form{grid-template-columns:1fr}.zm-viewing-modal__dialog{padding:26px 20px}}
    </style>
    <script>
    window.ZM_IMP_PLOTS=<?php echo wp_json_encode($plots); ?>;
    (function(){
      var plots=window.ZM_IMP_PLOTS||{}, selector='.imp-object,.imp-shape,.imp-object-poly,.imp-object-rect,.imp-object-ellipse';
      function code(el){if(!el)return'';var marked=el.closest&&el.closest('[data-zm-plot]');if(marked&&plots[marked.dataset.zmPlot])return marked.dataset.zmPlot;var raw='';for(var n=el;n&&n!==document.body;n=n.parentElement){raw+=' '+(n.getAttribute&&((n.getAttribute('data-title')||'')+' '+(n.getAttribute('aria-label')||'')+' '+(n.getAttribute('title')||'')));}raw+=' '+(el.textContent||'');var m=raw.match(/(?:pozemok|parcela)\s*[-–:]?\s*(0?[1-9]|1[0-9])\b/i);return m?String(parseInt(m[1],10)).padStart(2,'0'):'';}
      function shapes(el){if(!el)return[];if(/^(path|polygon|rect|circle|ellipse)$/i.test(el.tagName||''))return[el];return el.querySelectorAll?Array.from(el.querySelectorAll('path,polygon,rect,circle,ellipse')):[];}
      function paint(){document.querySelectorAll(selector).forEach(function(el){var c=code(el),p=plots[c];if(!p)return;shapes(el).forEach(function(s){s.classList.remove('zm-imp-status-available','zm-imp-status-reserved','zm-imp-status-sold');s.classList.add('zm-imp-status-'+p.status);s.dataset.zmPlot=c;});});}
      function card(p){var a=document.createElement('article'),cta=p.status==='available'?'<button type="button" class="zm-map-tooltip__cta" data-zm-reserve="'+p.code+'">Rezervovať obhliadku</button>':'';a.className='zm-map-tooltip zm-map-tooltip--'+p.status;a.dataset.zmPlot=p.code;a.innerHTML='<div class="zm-map-tooltip__top"><h3>Pozemok '+p.code+'</h3><span>'+p.statusLabel+'</span></div><dl><div><dt>Rozloha</dt><dd>'+p.areaLabel+'</dd></div><div><dt>Cena</dt><dd>'+p.priceLabel+'</dd></div></dl>'+cta;return a;}
      var activeCode='';document.addEventListener('pointerover',function(e){var source=e.target.closest&&e.target.closest(selector),c=source?code(source):'';if(plots[c])activeCode=c;},true);
      function replaceTooltip(target){var wrap=target&&target.closest&&target.closest('.imp-tooltip');if(!wrap)return;var source=document.querySelector('[data-zm-plot].imp-object:hover,[data-zm-plot].imp-shape:hover')||document.querySelector('.imp-object:hover,.imp-shape:hover');var c=source?code(source):activeCode;if(!plots[c])return;var host=wrap.querySelector('.imp-tooltip-content,.imp-tooltip-plain-text')||wrap;if(host.querySelector('.zm-map-tooltip[data-zm-plot="'+c+'"]'))return;host.replaceChildren(card(plots[c]));}
      var modal=document.querySelector('.zm-viewing-modal'),form=modal&&modal.querySelector('form');function open(c){var p=plots[c];if(!modal||!p||p.status!=='available')return;modal.hidden=false;modal.setAttribute('aria-hidden','false');modal.querySelector('[name="plot"]').value=c;modal.querySelector('[data-zm-plot-label]').textContent=c;modal.querySelector('[name="message"]').value='Dobrý deň, chcel/chcela by som si dohodnúť obhliadku pozemku č. '+c+'.';modal.querySelector('[name="name"]').focus();document.body.style.overflow='hidden';}
      document.addEventListener('click',function(e){var b=e.target.closest('[data-zm-reserve]');if(b){e.preventDefault();open(b.dataset.zmReserve);}if(e.target.closest('[data-zm-close]')){modal.hidden=true;modal.setAttribute('aria-hidden','true');document.body.style.overflow='';}});
      document.addEventListener('keydown',function(e){if(e.key==='Escape'&&modal&&!modal.hidden){modal.hidden=true;modal.setAttribute('aria-hidden','true');document.body.style.overflow='';}});
      if(form)form.addEventListener('submit',function(e){e.preventDefault();var out=form.querySelector('.zm-viewing-form__response'),btn=form.querySelector('button[type="submit"]');btn.disabled=true;out.textContent='Odosielam…';fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>',{method:'POST',body:new FormData(form),credentials:'same-origin'}).then(function(r){return r.json();}).then(function(r){out.textContent=r.data&&r.data.message?r.data.message:'Hotovo.';if(r.success)form.reset();}).catch(function(){out.textContent='Správu sa nepodarilo odoslať.';}).finally(function(){btn.disabled=false;});});
      new MutationObserver(function(ms){paint();ms.forEach(function(m){m.addedNodes.forEach(function(n){if(n.nodeType===1)replaceTooltip(n);});});}).observe(document.body,{childList:true,subtree:true});document.addEventListener('DOMContentLoaded',paint);window.addEventListener('load',paint);setTimeout(paint,800);
    })();
    </script>
    <?php
}, 30);
