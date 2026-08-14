<?php
/**
 * Plugin Name: Zelené Mokrance – Vypnutie komentárov
 * Description: Zakáže komentáre a trackbacky na celom webe (frontend, feedy, REST API, admin).
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Frontend: komentáre a pingy sú zatvorené, počty vrátené ako 0.
add_filter( 'comments_open', '__return_false' );
add_filter( 'pings_open', '__return_false' );
add_filter( 'comments_array', '__return_empty_array' );
add_filter( 'get_comments_number', '__return_false' );

// Frontend: namiesto šablóny komentárov sa vykreslí prázdny súbor.
add_filter( 'comments_template', function () {
	return __DIR__ . '/inc/empty-comments.php';
} );

// Post types: odobrať podporu komentárov a trackbackov.
add_action( 'init', function () {
	foreach ( get_post_types( array( 'public' => true ) ) as $post_type ) {
		remove_post_type_support( $post_type, 'comments' );
		remove_post_type_support( $post_type, 'trackbacks' );
	}
}, 100 );

// Feed komentárov: nevypisovať link z <head> a na feed vrátiť 403.
add_filter( 'feed_links_show_comments_feed', '__return_false' );
add_action( 'do_feed_rss2_comments', 'zm_disable_comments_feed_die', 1 );
add_action( 'do_feed_atom_comments', 'zm_disable_comments_feed_die', 1 );
add_action( 'template_redirect', 'zm_disable_comments_feed_redirect' );
function zm_disable_comments_feed_redirect() {
	if ( is_feed() ) {
		$feed = get_query_var( 'feed' );
		if ( is_string( $feed ) && stripos( $feed, 'comments' ) !== false ) {
			zm_disable_comments_feed_die();
		}
	}
}
function zm_disable_comments_feed_die() {
	wp_die( 'Komentáre sú vypnuté.', 'Komentáre sú vypnuté', array( 'response' => 403 ) );
}

// Existujúce príspevky: jednorazovo zavrieť komentáre a pingy.
add_action( 'init', function () {
	if ( get_option( 'zm_disable_comments_seeded' ) ) {
		return;
	}

	global $wpdb;
	$wpdb->query(
		"UPDATE {$wpdb->posts} SET comment_status = 'closed', ping_status = 'closed'
		 WHERE post_status IN ( 'publish', 'future', 'private' )
		   AND ( comment_status = 'open' OR ping_status = 'open' )"
	);
	update_option( 'zm_disable_comments_seeded', 1, false );
} );

// Admin: skryť menu Komentáre a metaboxy.
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
} );
add_action( 'admin_init', function () {
	remove_meta_box( 'commentsdiv', 'post', 'normal' );
	remove_meta_box( 'commentstatusdiv', 'post', 'normal' );
	remove_meta_box( 'commentsdiv', 'page', 'normal' );
	remove_meta_box( 'commentstatusdiv', 'page', 'normal' );
} );

// REST API: odstrániť comment endpoints.
add_filter( 'rest_endpoints', function ( $endpoints ) {
	unset( $endpoints['/wp/v2/comments'] );
	unset( $endpoints['/wp/v2/comments/(?P<id>[\d]+)'] );
	return $endpoints;
} );

// Widget „Najnovšie komentáre" odstrániť.
add_action( 'widgets_init', function () {
	unregister_widget( 'WP_Widget_Recent_Comments' );
}, 20 );
