<?php
/**
 * Plugin Name: Zelené Mokrance – Kontakt
 * Description: Centrálne kontaktné údaje v ACF, kontaktná stránka a formulár.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

const ZM_CONTACT_VERSION = '1.0.1';
const ZM_CONTACT_OPTION  = 'zm_contact_settings';

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_page( array(
		'page_title' => 'Kontaktné údaje',
		'menu_title' => 'Kontaktné údaje',
		'menu_slug'  => 'zm-kontaktne-udaje',
		'post_id'    => ZM_CONTACT_OPTION,
		'capability' => 'manage_options',
		'icon_url'   => 'dashicons-phone',
		'position'   => 58,
		'redirect'   => false,
	) );

	acf_add_local_field_group( array(
		'key'    => 'group_zm_contact_settings',
		'title'  => 'Kontaktné údaje webu',
		'fields' => array(
			array( 'key' => 'field_zm_site_phone', 'label' => 'Telefón', 'name' => 'zm_site_phone', 'type' => 'text', 'default_value' => '0915 362 165' ),
			array( 'key' => 'field_zm_site_email', 'label' => 'E-mail', 'name' => 'zm_site_email', 'type' => 'email', 'default_value' => 'varga@inforeal.sk' ),
			array( 'key' => 'field_zm_broker_name', 'label' => 'Meno makléra', 'name' => 'zm_broker_name', 'type' => 'text', 'default_value' => 'Roman Varga' ),
			array( 'key' => 'field_zm_broker_company', 'label' => 'Realitná kancelária', 'name' => 'zm_broker_company', 'type' => 'text', 'default_value' => 'Inforeal' ),
			array( 'key' => 'field_zm_broker_email', 'label' => 'E-mail makléra', 'name' => 'zm_broker_email', 'type' => 'email', 'default_value' => 'varga@inforeal.sk' ),
			array( 'key' => 'field_zm_broker_phone', 'label' => 'Telefón makléra', 'name' => 'zm_broker_phone', 'type' => 'text' ),
			array( 'key' => 'field_zm_broker_web', 'label' => 'Web realitnej kancelárie', 'name' => 'zm_broker_web', 'type' => 'url', 'default_value' => 'https://inforeal.sk' ),
			array( 'key' => 'field_zm_broker_photo', 'label' => 'Fotografia makléra', 'name' => 'zm_broker_photo', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium' ),
			array( 'key' => 'field_zm_references', 'label' => 'Referencie – logá', 'name' => 'zm_references', 'type' => 'gallery', 'return_format' => 'id', 'preview_size' => 'thumbnail' ),
			array( 'key' => 'field_zm_partners', 'label' => 'Partneri – logá', 'name' => 'zm_partners', 'type' => 'gallery', 'return_format' => 'id', 'preview_size' => 'thumbnail' ),
		),
		'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'zm-kontaktne-udaje' ) ) ),
	) );
} );

function zm_contact_value( $name, $fallback = '' ) {
	$value = function_exists( 'get_field' ) ? get_field( $name, ZM_CONTACT_OPTION ) : '';
	return $value !== '' && $value !== null ? $value : $fallback;
}

function zm_contact_phone_href( $phone ) {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', (string) $phone );
}

function zm_contact_image( $id, $size, $alt, $loading = 'lazy' ) {
	$html = wp_get_attachment_image( (int) $id, $size, false, array( 'loading' => $loading ) );
	if ( ! $html ) return '';
	return preg_replace( '/\salt="[^"]*"/i', ' alt="' . esc_attr( $alt ) . '"', $html, 1 );
}

function zm_contact_logos( $field ) {
	$ids = zm_contact_value( $field, array() );
	if ( ! is_array( $ids ) || ! $ids ) {
		return '';
	}
	$html = '<div class="zm-contact__logos">';
	foreach ( $ids as $id ) {
		$id = is_array( $id ) && isset( $id['ID'] ) ? $id['ID'] : $id;
		$title = get_the_title( (int) $id );
		$html .= '<div class="zm-contact__logo">' . zm_contact_image( $id, 'medium', $title ) . '</div>';
	}
	return $html . '</div>';
}

function zm_contact_references() {
	$ids = zm_contact_value( 'zm_references', array() );
	if ( ! is_array( $ids ) || ! $ids ) return '';
	$html = '<div class="zm-contact__references">';
	foreach ( $ids as $id ) {
		$id = is_array( $id ) && isset( $id['ID'] ) ? $id['ID'] : $id;
		$title = get_the_title( (int) $id );
		$html .= '<figure class="zm-contact__reference">' . zm_contact_image( $id, 'large', $title ) . '<figcaption>' . esc_html( $title ) . '</figcaption></figure>';
	}
	return $html . '</div>';
}

function zm_contact_collect_media_ids( $value, &$ids ) {
	if ( is_array( $value ) ) {
		if ( isset( $value['id'] ) && is_numeric( $value['id'] ) && get_post_type( (int) $value['id'] ) === 'attachment' ) {
			$ids[] = (int) $value['id'];
		}
		foreach ( $value as $item ) zm_contact_collect_media_ids( $item, $ids );
	} elseif ( is_string( $value ) && preg_match_all( '/wp-image-([0-9]+)/', $value, $matches ) ) {
		$ids = array_merge( $ids, array_map( 'intval', $matches[1] ) );
	}
}

add_action( 'acf/init', function () {
	if ( ! function_exists( 'update_field' ) || get_option( 'zm_contact_media_seeded' ) ) return;
	foreach ( array( 'referencie' => 'field_zm_references', 'partneri' => 'field_zm_partners' ) as $slug => $field_key ) {
		$page = get_page_by_path( $slug );
		if ( ! $page ) continue;
		$ids = array();
		zm_contact_collect_media_ids( $page->post_content, $ids );
		zm_contact_collect_media_ids( get_post_meta( $page->ID, '_bricks_page_content_2', true ), $ids );
		$ids = array_values( array_unique( array_filter( $ids ) ) );
		if ( $ids ) update_field( $field_key, $ids, ZM_CONTACT_OPTION );
	}
	$photo = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 's' => 'Roman Varga', 'fields' => 'ids' ) );
	if ( ! $photo ) $photo = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 's' => 'inforeal', 'fields' => 'ids' ) );
	if ( $photo ) update_field( 'field_zm_broker_photo', (int) $photo[0], ZM_CONTACT_OPTION );
	update_option( 'zm_contact_media_seeded', 1, false );
}, 40 );

add_shortcode( 'zm_contact_page', function () {
	$phone         = zm_contact_value( 'zm_site_phone', '0915 362 165' );
	$email         = zm_contact_value( 'zm_site_email', 'varga@inforeal.sk' );
	$broker_name   = zm_contact_value( 'zm_broker_name', 'Roman Varga' );
	$broker_co     = zm_contact_value( 'zm_broker_company', 'Inforeal' );
	$broker_email  = zm_contact_value( 'zm_broker_email', 'varga@inforeal.sk' );
	$broker_phone  = zm_contact_value( 'zm_broker_phone', '' );
	$broker_web    = zm_contact_value( 'zm_broker_web', 'https://inforeal.sk' );
	$photo_id      = (int) zm_contact_value( 'zm_broker_photo', 0 );
	$message       = isset( $_GET['pozemok'] ) ? sprintf( 'Dobrý deň, mám záujem o obhliadku pozemku č. %s.', sanitize_text_field( wp_unslash( $_GET['pozemok'] ) ) ) : '';
	$notice        = isset( $_GET['kontakt'] ) && $_GET['kontakt'] === 'odoslane' ? '<p class="zm-contact__notice">Ďakujeme. Vašu správu sme odoslali.</p>' : '';

	ob_start(); ?>
	<div class="zm-contact">
		<section class="zm-contact__hero">
			<p class="zm-contact__eyebrow">Kontakt</p>
			<h1>Dohodnite si obhliadku pozemku</h1>
			<p>Radi vám predstavíme lokalitu, dostupné parcely aj celý proces kúpy. Ozvite sa nám a vyberieme vhodný termín osobnej obhliadky.</p>
		</section>

		<section class="zm-contact__grid">
			<div class="zm-contact__broker">
				<div class="zm-contact__photo"><?php echo $photo_id ? zm_contact_image( $photo_id, 'large', $broker_name, 'eager' ) : '<span>RV</span>'; ?></div>
				<div class="zm-contact__broker-body">
					<p class="zm-contact__eyebrow">Predaj zastrešuje</p>
					<h2><?php echo esc_html( $broker_name ); ?></h2>
					<p class="zm-contact__company"><?php echo esc_html( $broker_co ); ?></p>
					<div class="zm-contact__links">
						<?php if ( $broker_phone ) : ?><a href="<?php echo esc_url( zm_contact_phone_href( $broker_phone ) ); ?>"><?php echo esc_html( $broker_phone ); ?></a><?php endif; ?>
						<a href="mailto:<?php echo esc_attr( $broker_email ); ?>"><?php echo esc_html( $broker_email ); ?></a>
						<a href="<?php echo esc_url( $broker_web ); ?>" target="_blank" rel="noopener">inforeal.sk</a>
					</div>
					<a class="zm-contact__button" href="#kontakt-formular">Chcem obhliadku</a>
				</div>
			</div>

			<div class="zm-contact__form-card" id="kontakt-formular">
				<p class="zm-contact__eyebrow">Napíšte nám</p>
				<h2>Mám záujem o pozemok</h2>
				<?php echo $notice; ?>
				<form class="zm-contact__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="zm_contact_submit">
					<?php wp_nonce_field( 'zm_contact_submit', 'zm_contact_nonce' ); ?>
					<label>Meno a priezvisko<input required name="name" autocomplete="name"></label>
					<div class="zm-contact__form-row">
						<label>E-mail<input required type="email" name="email" autocomplete="email"></label>
						<label>Telefón<input required type="tel" name="phone" autocomplete="tel"></label>
					</div>
					<label>Správa<textarea required name="message" rows="5"><?php echo esc_textarea( $message ); ?></textarea></label>
					<input class="zm-contact__hp" name="website" tabindex="-1" autocomplete="off">
					<label class="zm-contact__consent"><input required type="checkbox" name="gdpr" value="1"><span>Súhlasím so spracovaním osobných údajov na účely vybavenia mojej požiadavky.</span></label>
					<button type="submit">Odoslať správu</button>
				</form>
			</div>
		</section>

		<section class="zm-contact__direct">
			<div><p class="zm-contact__eyebrow">Telefón</p><a href="<?php echo esc_url( zm_contact_phone_href( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></div>
			<div><p class="zm-contact__eyebrow">E-mail</p><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></div>
		</section>

		<section class="zm-contact__about">
			<p class="zm-contact__eyebrow">O nás</p>
			<h2>Skúsenosti a partneri projektu</h2>
			<p>Projekt Zelené Mokrance vzniká v spolupráci s overenými odborníkmi. Na jednom mieste spájame prípravu pozemkov, povoľovacie procesy a profesionálny predaj.</p>
			<?php $partners = zm_contact_logos( 'zm_partners' ); if ( $partners ) : ?><h3>Partneri</h3><?php echo $partners; endif; ?>
			<?php $refs = zm_contact_references(); if ( $refs ) : ?><h3>Referencie</h3><?php echo $refs; endif; ?>
		</section>
	</div>
	<?php return ob_get_clean();
} );

function zm_contact_submit() {
	if ( ! isset( $_POST['zm_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zm_contact_nonce'] ) ), 'zm_contact_submit' ) ) {
		wp_die( 'Neplatná požiadavka.' );
	}
	if ( ! empty( $_POST['website'] ) || empty( $_POST['gdpr'] ) ) {
		wp_safe_redirect( home_url( '/kontakt/' ) ); exit;
	}
	$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
	if ( ! $name || ! is_email( $email ) || ! $phone || ! $message ) {
		wp_safe_redirect( home_url( '/kontakt/?kontakt=chyba' ) ); exit;
	}
	$recipient = zm_contact_value( 'zm_broker_email', zm_contact_value( 'zm_site_email', get_option( 'admin_email' ) ) );
	$body = "Meno: {$name}\nE-mail: {$email}\nTelefón: {$phone}\n\nSpráva:\n{$message}";
	wp_mail( $recipient, 'Zelené Mokrance – nový záujemca', $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
	wp_safe_redirect( home_url( '/kontakt/?kontakt=odoslane#kontakt-formular' ) ); exit;
}
add_action( 'admin_post_nopriv_zm_contact_submit', 'zm_contact_submit' );
add_action( 'admin_post_zm_contact_submit', 'zm_contact_submit' );

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_page( array( 'kontakt', 'contact' ) ) ) return;
	wp_register_style( 'zm-contact', false, array(), ZM_CONTACT_VERSION );
	wp_enqueue_style( 'zm-contact' );
	wp_add_inline_style( 'zm-contact', '
.zm-contact{--green:var(--zm-color-header-green,#507d0c);--accent:var(--zm-color-accent,#9fc74a);--ink:var(--zm-color-ink,#20251f);--soft:#f3f6ee;color:var(--ink);font-family:"Nunito Sans",sans-serif}.zm-contact section{max-width:1240px;margin-inline:auto;padding-inline:24px}.zm-contact__hero{padding-top:clamp(70px,9vw,130px);padding-bottom:clamp(45px,7vw,90px);max-width:900px!important;margin-left:max(0px,calc((100% - 1240px)/2))!important}.zm-contact__eyebrow{margin:0 0 12px;text-align:left;font-size:.78rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--green)}.zm-contact h1,.zm-contact h2,.zm-contact h3{font-family:"Nunito Sans",sans-serif;color:var(--green);line-height:1.12}.zm-contact h1{font-size:clamp(2.5rem,5.5vw,5.2rem);max-width:850px;margin:0 0 24px}.zm-contact__hero>p:last-child{font-size:clamp(1.05rem,1.5vw,1.3rem);line-height:1.7;max-width:760px}.zm-contact__grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:28px;padding-bottom:80px}.zm-contact__broker,.zm-contact__form-card{border-radius:24px;overflow:hidden;background:var(--soft)}.zm-contact__broker{display:grid;grid-template-columns:44% 56%}.zm-contact__photo{min-height:560px;background:var(--green);display:grid;place-items:center;color:#fff;font-size:4rem;font-weight:900}.zm-contact__photo img{width:100%;height:100%;object-fit:cover}.zm-contact__broker-body,.zm-contact__form-card{padding:clamp(28px,4vw,54px)}.zm-contact h2{font-size:clamp(2rem,3.3vw,3.25rem);margin:0 0 12px}.zm-contact__company{font-size:1.15rem;font-weight:800;margin:0 0 28px}.zm-contact__links{display:flex;flex-direction:column;gap:9px;margin-bottom:30px}.zm-contact__links a,.zm-contact__direct a{color:var(--ink);font-size:1.08rem;font-weight:700;text-decoration:none}.zm-contact__button,.zm-contact button{display:inline-flex;justify-content:center;border:0;border-radius:999px;background:var(--accent);color:#20310b;font-weight:900;padding:14px 24px;text-decoration:none;cursor:pointer;transition:.2s ease}.zm-contact__button:hover,.zm-contact button:hover{transform:translateY(-2px);filter:brightness(.95)}.zm-contact__form{display:grid;gap:17px}.zm-contact__form label{display:grid;gap:7px;font-size:.88rem;font-weight:800}.zm-contact__form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}.zm-contact input,.zm-contact textarea{width:100%;border:1px solid #dfe5d7;border-radius:12px;background:#fff;padding:13px 15px;font:inherit;color:var(--ink)}.zm-contact textarea{resize:vertical}.zm-contact__consent{grid-template-columns:20px 1fr!important;align-items:start;font-weight:500!important;line-height:1.45}.zm-contact__consent input{width:18px;height:18px;margin-top:2px}.zm-contact__hp{position:absolute!important;left:-9999px!important}.zm-contact__notice{padding:12px 15px;border-radius:10px;background:#e8f3d4;color:#315b05}.zm-contact__direct{display:grid;grid-template-columns:1fr 1fr;gap:24px;padding-block:0 90px}.zm-contact__direct>div{border-top:1px solid #dce2d6;padding-top:24px}.zm-contact__direct a{font-size:clamp(1.25rem,2.4vw,2rem)}.zm-contact__about{padding-block:80px 110px;border-top:1px solid #e4e7e0}.zm-contact__about>p:not(.zm-contact__eyebrow){max-width:720px;font-size:1.08rem;line-height:1.7}.zm-contact__about h3{margin-top:48px}.zm-contact__logos{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:18px}.zm-contact__logo{min-height:120px;display:grid;place-items:center;border:1px solid #e1e6dc;border-radius:16px;padding:22px;background:#fff}.zm-contact__logo img{max-width:160px;max-height:70px;width:auto;height:auto;object-fit:contain;filter:grayscale(1);opacity:.75;transition:.2s}.zm-contact__logo:hover img{filter:none;opacity:1}@media(max-width:900px){.zm-contact__grid{grid-template-columns:1fr}.zm-contact__broker{grid-template-columns:38% 62%}.zm-contact__photo{min-height:460px}}@media(max-width:620px){.zm-contact__hero{padding-top:55px}.zm-contact__grid{padding-inline:16px}.zm-contact__broker{grid-template-columns:1fr}.zm-contact__photo{min-height:360px}.zm-contact__form-row,.zm-contact__direct{grid-template-columns:1fr}.zm-contact__direct{padding-inline:20px}.zm-contact__broker-body,.zm-contact__form-card{padding:28px 22px}}
' );
	wp_add_inline_style( 'zm-contact', '
.zm-contact__references{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.zm-contact__reference{margin:0;overflow:hidden;border-radius:16px;background:#fff;border:1px solid #e1e6dc}.zm-contact__reference img{display:block;width:100%;aspect-ratio:4/3;object-fit:cover}.zm-contact__reference figcaption{padding:12px 16px;font-size:.85rem;font-weight:800;color:var(--green)}@media(max-width:620px){.zm-contact__references{grid-template-columns:1fr}}
' );
} );
