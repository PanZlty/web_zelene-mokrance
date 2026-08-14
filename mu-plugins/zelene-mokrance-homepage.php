<?php
/**
 * Plugin Name: Zelené Mokrance – Domovská stránka
 * Description: Bricks domovská stránka projektu Zelené Mokrance.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

const ZM_HOME_VERSION = '1.0.0';

/*
 * The homepage is authored entirely with native Bricks elements on page 18048.
 * The shortcode is intentionally disabled so no homepage copy can be rendered
 * from this version-controlled plugin instead of Bricks.
 */
add_shortcode( 'zm_homepage', function () {
return '';
} );
