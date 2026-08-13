<?php
/**
 * Plugin Name: Zelene Mokrance - frontend hardening
 * Description: Removes Houzez demo/account remnants and carries reviewed layout CSS outside YellowPencil.
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Keep YellowPencil available in wp-admin and its live editor, but do not
 * print its generated CSS on the public site. The reviewed rules below are
 * the maintained replacement for the currently used published-page styles.
 */
function zm_frontend_hardening_is_public_request() {
    if (is_admin()) {
        return false;
    }

    if (defined('REST_REQUEST') && REST_REQUEST) {
        return false;
    }

    if (isset($_GET['yellow_pencil_frame']) || isset($_GET['wyp_live_preview'])) {
        return false;
    }

    return true;
}

add_action('wp_head', function () {
    if (!zm_frontend_hardening_is_public_request()) {
        return;
    }

    if (function_exists('wyp_get_css')) {
        remove_action('wp_head', 'wyp_get_css', 999999999);
    }

    if (function_exists('wyp_get_live_preview')) {
        remove_action('wp_head', 'wyp_get_live_preview', 999999999);
    }
}, 0);

/**
 * Return the reviewed global CSS formerly emitted by YellowPencil.
 */
function zm_frontend_hardening_global_css() {
    return <<<'CSS'
.header-inner-wrap .d-flex .header-wrap-6-logo{padding-left:0;padding-right:0;margin-right:110px;margin-left:100px}
.header-wrap-6-icons .header-social-icons ul{visibility:hidden;display:flex}
.page-title-wrap .item-address,#module_properties .d-flex .item-address{visibility:hidden}
#imp-object-list-item{position:relative;height:36px;display:flex;top:8px;left:auto!important;padding-top:0}
#dostupnost,#nedostupnost,#dostupnost2{font-size:12px;border-radius:50px;height:20px;color:#000;text-align:center;position:relative;top:8px;white-space:nowrap;letter-spacing:0;direction:ltr;text-transform:capitalize;line-height:1.7em!important;font-weight:500;padding:0 12px;left:0}
#dostupnost{background-color:#b7e871}
#nedostupnost{background-color:#ffb505}
#dostupnost2{background-color:#f2b80c}
#Parcela{position:relative;top:0;left:0;padding-right:16px}
.breadcrumb-wrap nav ol{visibility:hidden}
@media (max-width:782px){.mobile-top-wrap .mobile-property-title .item-address{visibility:hidden}.mobile-property-title .item-address{visibility:hidden}#mobile-main-nav .nav-item .nav-link{border-color:rgba(2,2,2,.24)}#nav-mobile{margin-bottom:0}
}
@media (max-width:767px){.property-view .mobile-top-wrap .mobile-property-tools{visibility:hidden;clear:none;overflow:hidden}.property-view .mobile-top-wrap .mobile-property-title{padding-bottom:38px;line-height:1;font-size:8px;position:relative;top:-18px;font-family:Lato,sans-serif;display:inline-block;transform:translate(0,0)}.mobile-property-title .item-price-wrap li{font-size:36px}.mobile-property-title .page-title span{font-size:26px;font-family:Dosis,sans-serif}.mobile-property-title .page-title span{line-height:1!important}}
.imp-translate .imp-glowing-objects{transform:translate(0,0)}
.imp-translate .imp-objects .imp-object{text-transform:lowercase}
.header-inner-wrap .d-flex .header-wrap-6-left-menu{position:relative;left:0;padding-right:0}
.header-inner-wrap .d-flex .header-wrap-6-right-menu{padding-left:0}
.elementor-element-3bd5394 div .imp-ui-wrap{top:-20px}
@media (min-width:1025px){.elementor-element-65b849f0 .elementor-container .elementor-inner-column .elementor-widget-wrap .elementor-widget-heading .elementor-widget-container h1{font-size:36px!important}}
CSS;
}

/**
 * Return only published-page rules that were found in YellowPencil meta.
 * Draft-only newsletter styling is intentionally not carried forward.
 */
function zm_frontend_hardening_page_css() {
    $css = '';

    if (is_page(18048)) {
        $css .= '.elementor .elementor-element-7aaa241 .elementor-container .elementor-top-column .elementor-widget-wrap .elementor-element-65b849f0 .elementor-container .elementor-inner-column .elementor-widget-wrap .elementor-widget-heading .elementor-widget-container h1{line-height:1.1em!important}' . "\n";
    }

    if (is_page(array(18581, 20299, 20301, 20307, 20309))) {
        $css .= '.accordion p strong{visibility:hidden}.accordion .accordion-body p{position:relative;top:0;margin-top:-40px}' . "\n";
    }

    if (is_page(18855)) {
        $css .= '#module_properties .d-flex h2{font-size:22px}' . "\n";
    }

    if (is_page(19534)) {
        $css .= '.imp-translate .imp-objects{top:0}.elementor-widget-text-editor div .imp-ui-wrap{top:-16px}' . "\n";
    }

    return $css;
}

add_action('wp_enqueue_scripts', function () {
    if (!zm_frontend_hardening_is_public_request()) {
        return;
    }

    wp_register_style('zm-frontend-hardening', false, array(), '1.0.0');
    wp_enqueue_style('zm-frontend-hardening');

    $css = zm_frontend_hardening_global_css() . "\n" . zm_frontend_hardening_page_css();

    // Houzez demo modules: compare panel, login/register/reset modal and trigger.
    $css .= <<<'CSS'
#compare-property-panel,
#login-register-form,
#reset-password-form,
#login-register-form #login-form-tab,
#login-register-form #register-form-tab,
#login-register-form .login-register-tabs,
a.modal-toggle-1[href="#login-form-tab"]{display:none!important}
CSS;

    wp_add_inline_style('zm-frontend-hardening', $css);
}, 100);

/**
 * Keep the site's canonical scheme/host for the Home menu item even if an
 * old menu entry still contains http://zelenemokrance.sk.
 */
add_filter('nav_menu_link_attributes', function ($atts) {
    if (empty($atts['href'])) {
        return $atts;
    }

    $url = wp_parse_url($atts['href']);
    $host = isset($url['host']) ? strtolower($url['host']) : '';
    $path = isset($url['path']) ? untrailingslashit($url['path']) : '';

    if ($host === 'zelenemokrance.sk' && $path === '' && isset($url['scheme']) && strtolower($url['scheme']) === 'http') {
        $atts['href'] = home_url('/');
    }

    return $atts;
}, 10, 1);
