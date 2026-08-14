# Aktuálny snapshot webu Zelené Mokrance (po Bricks migrácii)

Dátum: 2026-08-14
Zdroj: Novamira/Bricks MCP introspekcia, WordPress databáza a verejný frontend.

## Účel

Tento dokument zachytáva stav webu po migrácii na Bricks a po prvých opravách
(premenovanie Domov → Lokalita, dynamický rok vo footeri, zjednotenie kontaktov).
Nahrádza baseline z 2026-08-13 (Houzez + Elementor) ako aktuálnu realitu.

## Aktuálny stack

- WordPress: 7.0.4, PHP 8.3, locale sk_SK
- Téma: Bricks Child (parent Bricks 2.4-beta2)
- Page builder: Bricks (native)
- Datový model: CPT `pozemok` + ACF Pro (sync z Google Sheets)
- Mapa: Image Map Pro v6
- Multilingual: TranslatePress nie je aktívny (iba sk_SK)
- SMTP: FluentSMTP
- Automatizácia/MCP: MCP Adapter + Novamira + Novamira Pro
- Houzez / Elementor / YellowPencil: nie sú aktívne (demo UI skryté cez MU-plugin)

## Stránky

| ID | Stránka | Stav | Poznámka |
|---:|---|---|---|
| 18048 | Lokalita | publish | front page, slug `lokalita` (predtým „Domov") |
| 20311 | Pozemky | publish | Bricks; tabuľka, mapa, proces kúpy, modál obhliadky |
| 18787 | Galéria | publish | Bricks |
| 16941 | Kontakt | publish | Bricks; broker Roman Varga / Inforeal; form → varga@inforeal.sk |
| 20381 | Zásady ochrany osobných údajov | publish | klasický obsah (nie Bricks) |
| 20188 | Zásady používania súborov cookie | publish | klasický obsah |

## Menu

Main Menu (id 61): **Lokalita, Pozemky, Galéria, Kontakt**. Položka Lokalita ukazuje na front page.

## Kontakty

- email: **varga@inforeal.sk**
- telefón: **0915 362 165**
- maklér: Roman Varga, Inforeal
- prevádzkovateľ: MOKRANCE INVEST družstvo, Tajovského 17, Košice – Staré Mesto, 040 01

## Design systém (Bricks)

- Paleta „Zelené Mokrance" (7 farieb): bg #FFFFFF, ink #222222, accent #9FC74A, support #F2B80C, header-green #507d0c, header-text #FFFFFF
- Globálne triedy `zm-header*` a `zm-button*` (spolu 18), komponent Button, 7 CSS premenných `--zm-color-*`
- Breakpointy: desktop 1279, tablet portrait 991, mobile landscape 767, mobile portrait 478

## Dátový model pozemkov

- CPT `pozemok`, seed 55 záznamov (`Pozemok 01`–`Pozemok 55`)
- ACF: `plot_id`, `area_m2`, `price`, `status` (available/reserved/sold)
- Sync: Google Sheets CSV (cron 15 min + manuálna tools stránka)
- Frontend tabuľka a mapa zobrazujú aktuálne 01–19; rozhodnuté (2026-08-14): zvyšok 20–55 doplníme neskôr

## Footer

- Dynamický rok: Bricks `{do_action:zm_footer_year}` (MU-plugin `zelene-mokrance-footer.php`) → „© 2026 Zelené Mokrance"
- Kontakt: varga@inforeal.sk, 0915 362 165, Instagram, Facebook
- IČO a sídlo: čakajú na potvrdenie od majiteľa

## Známe otvorené položky

- GitHub Actions deploy (SFTP) zlyháva — treba opraviť secrets / `SFTP_TARGET`
- PDF cenník / situačný plán — čaká na podklady
- IČO/sídlo do footeru — čaká na údaje
- Redukcia galérie, SEO meta, Lighthouse — otvorené (pozri IMPLEMENTATION-BACKLOG.md)

Dokument neobsahuje heslá, application passwords, SFTP údaje ani API tokeny.
