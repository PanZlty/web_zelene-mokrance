<?php
/**
 * Plugin Name: Zelené Mokrance – Footer
 * Description: Dynamický rok v pätičke cez Bricks dynamic tag {do_action:zm_footer_year}.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Vypíše aktuálny rok pre Bricks {do_action:zm_footer_year}.
 *
 * date_i18n() rešpektuje časovú zónu WordPressu, takže rok sa zmení
 * automaticky s novým rokom bez zásahu do obsahu.
 */
add_action( 'zm_footer_year', function () {
	echo esc_html( date_i18n( 'Y' ) );
} );
