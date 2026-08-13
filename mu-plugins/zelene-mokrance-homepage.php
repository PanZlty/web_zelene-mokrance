<?php
/**
 * Plugin Name: Zelené Mokrance – Domovská stránka
 * Description: Bricks domovská stránka projektu Zelené Mokrance.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

const ZM_HOME_VERSION = '1.0.0';

function zm_home_media_url( $attachment_id, $size = 'full' ) {
	$url = wp_get_attachment_image_url( (int) $attachment_id, $size );
	return $url ? $url : '';
}

add_shortcode( 'zm_homepage', function () {
	$hero     = zm_home_media_url( 20532 ); // Dron-02s.
	$location = zm_home_media_url( 20538 ); // DSC_3501.
	$detail   = zm_home_media_url( 20537 ); // DSC_2411.
	ob_start(); ?>
	<main class="zm-home">
		<section class="zm-home__hero" style="--hero:url('<?php echo esc_url( $hero ); ?>')">
			<div class="zm-home__hero-overlay"></div>
			<div class="zm-home__hero-inner">
				<p class="zm-home__eyebrow">Stavebné pozemky pri Košiciach</p>
				<h1>Pokoj vidieka.<br>Metropola na dosah.</h1>
				<p>Obklopte sa vidiekom v blízkosti metropoly východu a odbremeňte svoje telo i dušu od každodenného stresu a povinností, ktoré so sebou život vo veľkomeste prináša.</p>
				<div class="zm-home__actions"><a class="zm-home__button" href="/pozemky/">Vybrať pozemok</a><a class="zm-home__link" href="/kontakt/">Dohodnúť obhliadku <span>→</span></a></div>
			</div>
			<div class="zm-home__hero-stats"><div><strong>25–30 min.</strong><span>do Košíc</span></div><div><strong>1–2 podlažia</strong><span>rodinné domy</span></div><div><strong>55 parciel</strong><span>v projekte</span></div></div>
		</section>

		<section class="zm-home__intro zm-home__wrap">
			<div><p class="zm-home__eyebrow">Zelené Mokrance</p><h2>Miesto, kde slovo domov získava nový význam</h2></div>
			<div class="zm-home__intro-copy"><p>Developerský projekt Zelené Mokrance je situovaný v tichej okrajovej časti obce Mokrance. Budúcim obyvateľom ponúka pokojné komunitné bývanie s dôrazom na príjemné prostredie a zodpovedný prístup k budovaniu novej štvrte.</p><p>Pozemky sa nachádzajú juhovýchodne od Košíc, približne pol hodiny jazdy autom a len na skok od Moldavy nad Bodvou. Sme presvedčení, že komfort bývania, ktorý vám ponúkame, dokážeme poskytnúť na úrovni, pri ktorej si poviete, že slovo domov nadobudlo úplne nový význam.</p></div>
		</section>

		<section class="zm-home__why">
			<div class="zm-home__wrap"><p class="zm-home__eyebrow">Prečo Zelené Mokrance</p><h2>Pozemok pripravený na váš ďalší krok</h2><div class="zm-home__features">
				<article><span>01</span><h3>Technická pripravenosť</h3><p>Prípojka splaškovej kanalizácie, vodovodu, elektrická NN prípojka a IT pripojenie podľa štandardu projektu.</p></article>
				<article><span>02</span><h3>Menej administratívy</h3><p>Pomôžeme s inžinierskou činnosťou, potrebnými vyjadreniami, povoľovacími procesmi a súvisiacimi poplatkami v dohodnutom rozsahu.</p></article>
				<article><span>03</span><h3>Jasné pravidlá výstavby</h3><p>Parcely sú určené pre jedno- až dvojpodlažné rodinné domy. Podmienky si prejdete ešte pred rezerváciou.</p></article>
			</div><a class="zm-home__text-link" href="/pozemky/">Pozrieť dostupné pozemky <span>→</span></a></div>
		</section>

		<section class="zm-home__split zm-home__wrap">
			<div class="zm-home__split-image"><img src="<?php echo esc_url( $location ); ?>" alt="Lokalita Zelené Mokrance" loading="lazy"></div>
			<div class="zm-home__split-content"><p class="zm-home__eyebrow">Lokalita</p><h2>Blízko mesta, bližšie k prírode</h2><p>Mokrance spájajú pokoj vidieckeho prostredia s dobrou dostupnosťou do Košíc, Moldavy nad Bodvou aj priemyselného parku Valaliky. Každodenné služby, školy, obchody a zdravotná starostlivosť sú dostupné v obci a blízkom okolí.</p><ul><li>Košice približne 25–30 minút autom</li><li>Moldava nad Bodvou na dosah</li><li>Materská a základná škola v okolí</li><li>Potraviny, lekárne, pošta a zdravotná starostlivosť</li></ul><a class="zm-home__text-link" href="/lokalita/">Spoznať lokalitu <span>→</span></a></div>
		</section>

		<section class="zm-home__quote" style="--detail:url('<?php echo esc_url( $detail ); ?>')"><div class="zm-home__quote-overlay"></div><div class="zm-home__quote-inner"><p>„Domov nie je iba stavba. Je to pokoj, priestor a istota, že ste si vybrali správne miesto.“</p></div></section>

		<section class="zm-home__process zm-home__wrap"><p class="zm-home__eyebrow">Jednoduchý proces</p><h2>Od výberu parcely po zápis do katastra</h2><div class="zm-home__steps"><div><b>1</b><span>Výber a obhliadka</span></div><div><b>2</b><span>Rezervácia</span></div><div><b>3</b><span>Rezervačná zmluva a záloha</span></div><div><b>4</b><span>Kúpna zmluva</span></div><div><b>5</b><span>Kataster</span></div></div></section>

		<section class="zm-home__cta"><div class="zm-home__wrap"><div><p class="zm-home__eyebrow">Nájdite svoje miesto</p><h2>Príďte sa pozrieť osobne</h2><p>Mapa parciel aj aktuálna dostupnosť sú online. Najlepší pocit z lokality však získate priamo na mieste.</p></div><div class="zm-home__actions"><a class="zm-home__button" href="/pozemky/">Ponuka pozemkov</a><a class="zm-home__button zm-home__button--light" href="/kontakt/">Chcem obhliadku</a></div></div></section>
	</main>
	<?php return ob_get_clean();
} );

// Native Bricks elements do not carry the shortcode's inline CSS variables.
// Keep the same visual backgrounds when the layout is authored directly in Bricks.
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_front_page() ) return;
	wp_add_inline_style( 'zm-homepage', '
.zm-home__hero{background-image:url("https://www.zelenemokrance.sk/wp-content/uploads/2026/08/Dron-02s.webp")}
.zm-home__quote{background-image:url("https://www.zelenemokrance.sk/wp-content/uploads/2026/08/DSC_2411.webp")}
.zm-home__feature-icon{margin-top:18px;color:var(--green);box-shadow:0 8px 22px rgba(66,102,22,.12)}
.zm-home__features>div{padding:34px 30px;background:#fff;border-radius:18px;transition:transform .25s ease,box-shadow .25s ease}
.zm-home__features>div:hover{transform:translateY(-5px);box-shadow:0 18px 42px rgba(48,73,20,.1)}
.zm-home__steps>div{position:relative;text-align:center;padding:0 18px}
.zm-home__steps>div:not(:last-child):after{content:"";display:block!important;position:absolute;top:31px;left:calc(50% + 38px);right:calc(-50% + 38px);height:2px;background:linear-gradient(90deg,var(--accent),#dce5d1);z-index:0}
.zm-home__steps>div:last-child:after,.zm-home__steps>div>div:after{display:none!important;content:none!important}
.zm-home__steps b{position:relative;z-index:1;width:64px;height:64px;margin-inline:auto;border:5px solid #eef4e5;box-shadow:0 8px 20px rgba(45,75,15,.18);font-size:1.25rem}
.zm-home__steps>div span{margin-top:20px;font-size:1rem;line-height:1.35}
@media(max-width:900px){.zm-home__steps>div{text-align:left;padding:0}.zm-home__steps>div:not(:last-child):after{display:none!important}.zm-home__steps b{margin:0}.zm-home__steps>div span{margin-top:0}}
' );
}, 30 );

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_front_page() ) return;
	wp_register_style( 'zm-homepage', false, array(), ZM_HOME_VERSION );
	wp_enqueue_style( 'zm-homepage' );
	wp_add_inline_style( 'zm-homepage', '
.zm-home{--green:var(--zm-color-header-green,#507d0c);--accent:var(--zm-color-accent,#9fc74a);--ink:var(--zm-color-ink,#20251f);--soft:#f2f5ed;--cream:#f7f4ec;color:var(--ink);font-family:"Nunito Sans",sans-serif}.zm-home *{box-sizing:border-box}.zm-home__wrap{width:min(1240px,calc(100% - 48px));margin-inline:auto}.zm-home h1,.zm-home h2,.zm-home h3{font-family:"Nunito Sans",sans-serif;line-height:1.08}.zm-home h2{font-size:clamp(2.25rem,4.2vw,4.4rem);margin:0;color:var(--green)}.zm-home__eyebrow{margin:0 0 16px;font-size:.76rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.zm-home__hero{position:relative;min-height:calc(100svh - 90px);display:flex;align-items:center;background-image:var(--hero);background-position:center;background-size:cover;color:#fff;isolation:isolate}.zm-home__hero-overlay{position:absolute;inset:0;background:linear-gradient(90deg,rgba(18,35,12,.78) 0%,rgba(18,35,12,.48) 48%,rgba(18,35,12,.12) 100%);z-index:-1}.zm-home__hero-inner{width:min(1240px,calc(100% - 48px));margin:auto;padding-block:90px 170px}.zm-home__hero h1{font-size:clamp(3.4rem,7.6vw,8.2rem);letter-spacing:-.045em;max-width:980px;margin:0 0 28px}.zm-home__hero-inner>p:not(.zm-home__eyebrow){max-width:720px;font-size:clamp(1.05rem,1.7vw,1.38rem);line-height:1.65}.zm-home__actions{display:flex;align-items:center;gap:22px;flex-wrap:wrap;margin-top:34px}.zm-home__button{display:inline-flex;align-items:center;justify-content:center;min-height:52px;padding:13px 25px;border-radius:999px;background:var(--accent);color:#1d2c0c;text-decoration:none;font-weight:900}.zm-home__link,.zm-home__text-link{color:inherit;text-decoration:none;font-weight:900}.zm-home__link span,.zm-home__text-link span{display:inline-block;margin-left:8px;transition:transform .2s}.zm-home__link:hover span,.zm-home__text-link:hover span{transform:translateX(5px)}.zm-home__hero-stats{position:absolute;left:50%;bottom:0;transform:translateX(-50%);width:min(1240px,calc(100% - 48px));display:grid;grid-template-columns:repeat(3,1fr);background:rgba(255,255,255,.94);color:var(--ink);backdrop-filter:blur(10px)}.zm-home__hero-stats div{padding:24px 30px;border-right:1px solid #dfe4d8}.zm-home__hero-stats div:last-child{border:0}.zm-home__hero-stats strong,.zm-home__hero-stats span{display:block}.zm-home__hero-stats strong{font-size:1.3rem;color:var(--green)}.zm-home__hero-stats span{font-size:.86rem;margin-top:3px}.zm-home__intro{display:grid;grid-template-columns:.9fr 1.1fr;gap:clamp(45px,9vw,130px);padding-block:clamp(85px,11vw,160px)}.zm-home__intro-copy{font-size:1.08rem;line-height:1.8}.zm-home__intro-copy p:first-child{margin-top:0}.zm-home__why{padding-block:clamp(80px,10vw,145px);background:var(--soft)}.zm-home__why h2{max-width:800px}.zm-home__features{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin:64px 0 42px}.zm-home__features article{padding:34px 30px;background:#fff;border-radius:18px}.zm-home__features article>span{display:block;color:var(--accent);font-size:2rem;font-weight:900}.zm-home__features h3{font-size:1.55rem;color:var(--green);margin:26px 0 12px}.zm-home__features p{line-height:1.65}.zm-home__text-link{color:var(--green)}.zm-home__split{display:grid;grid-template-columns:1.05fr .95fr;gap:clamp(45px,8vw,110px);align-items:center;padding-block:clamp(90px,11vw,160px)}.zm-home__split-image{height:clamp(440px,55vw,700px);overflow:hidden;border-radius:24px}.zm-home__split-image img{width:100%;height:100%;object-fit:cover}.zm-home__split-content>p:not(.zm-home__eyebrow){font-size:1.08rem;line-height:1.75}.zm-home__split ul{padding:0;list-style:none;margin:28px 0}.zm-home__split li{padding:13px 0;border-bottom:1px solid #dfe4d8;font-weight:800}.zm-home__quote{position:relative;min-height:620px;display:grid;place-items:center;background-image:var(--detail);background-position:center;background-size:cover;color:#fff;isolation:isolate}.zm-home__quote-overlay{position:absolute;inset:0;background:rgba(33,59,16,.55);z-index:-1}.zm-home__quote-inner{width:min(1000px,calc(100% - 48px));text-align:center}.zm-home__quote p{font-size:clamp(2.3rem,5vw,5.2rem);line-height:1.13;font-weight:800;margin:0}.zm-home__process{padding-block:clamp(90px,11vw,150px)}.zm-home__process h2{max-width:850px}.zm-home__steps{display:grid;grid-template-columns:repeat(5,1fr);margin-top:64px}.zm-home__steps div{position:relative;padding-right:20px}.zm-home__steps div:not(:last-child):after{content:"";position:absolute;top:22px;left:52px;right:12px;height:1px;background:#cbd5c0}.zm-home__steps b{display:grid;place-items:center;width:44px;height:44px;border-radius:50%;background:var(--green);color:#fff}.zm-home__steps span{display:block;margin-top:18px;font-weight:800}.zm-home__cta{padding-block:80px;background:var(--green);color:#fff}.zm-home__cta .zm-home__wrap{display:flex;justify-content:space-between;align-items:end;gap:40px}.zm-home__cta h2{color:#fff;max-width:720px}.zm-home__cta p:not(.zm-home__eyebrow){max-width:650px;line-height:1.7}.zm-home__cta .zm-home__actions{margin:0;justify-content:flex-end}.zm-home__button--light{background:#fff;color:var(--green)}@media(max-width:900px){.zm-home__intro,.zm-home__split{grid-template-columns:1fr}.zm-home__features{grid-template-columns:1fr}.zm-home__steps{grid-template-columns:1fr;gap:22px}.zm-home__steps div{display:grid;grid-template-columns:44px 1fr;align-items:center;gap:18px}.zm-home__steps div:after{display:none}.zm-home__steps span{margin:0}.zm-home__cta .zm-home__wrap{align-items:flex-start;flex-direction:column}.zm-home__cta .zm-home__actions{justify-content:flex-start}}@media(max-width:620px){.zm-home__wrap,.zm-home__hero-inner,.zm-home__hero-stats{width:min(100% - 32px,1240px)}.zm-home__hero{min-height:760px;align-items:flex-start}.zm-home__hero-inner{padding-top:90px}.zm-home__hero h1{font-size:3.35rem}.zm-home__hero-stats{grid-template-columns:1fr}.zm-home__hero-stats div{padding:12px 18px;border-right:0;border-bottom:1px solid #dfe4d8}.zm-home__hero-stats strong{font-size:1.05rem}.zm-home__intro{gap:35px}.zm-home__split-image{height:420px}.zm-home__quote{min-height:500px}}
' );
} );
