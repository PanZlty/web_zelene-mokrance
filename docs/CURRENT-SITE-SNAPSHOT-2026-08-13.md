# Aktuálny snapshot webu Zelené Mokrance

Dátum: 2026-08-13
Zdroj: verejný frontend, Novamira/Novamira Pro read-only introspekcia, WordPress databáza a TranslatePress tabuľky.

## Účel

Tento dokument je baseline pred ďalšími opravami a prípadnou migráciou z Houzez + Elementor do Bricks. Zachytáva aktuálnu štruktúru, vizuálne pravidlá, menu, preklady a riziká. Bricks migrácia sa v tejto fáze nespúšťa.

## Aktuálny stack

- WordPress locale: `sk_SK`
- Active theme: Houzez Child (`houzez-child`), parent template `houzez`
- Page builder: Elementor + Elementor Pro
- Property/map: Houzez + Image Map Pro
- Multilingual: TranslatePress Multilingual 3.3.1
- Custom CSS: YellowPencil Pro 7.6.0 a Elementor generated CSS
- Bricks: v dostupnom aktívnom plug-in inventári nie je aktívny; migrácia by bola nová realizačná vrstva, nie prepnutie jedným nastavením.

## Vizuálny baseline

Aktuálny vizuálny snapshot bol uložený aj v Novamira Design Library:

`zelene-mokrance-current-frontend-baseline`

Novamira potvrdil `saved: true`, `activated: true`, `readiness.ready: true` a `sync_ready: true`.

Pozorované hodnoty z frontendu:

- pozadie: `#FFFFFF`
- text: `#222222`
- hlavný akcent: `#9FC74A`
- podporná farba stavov: `#F2B80C`
- body font: Dosis, približne 16px / 25px
- homepage H1: Grape Nuts, približne 31px / 34px, weight 500
- primary button: zelené pozadie, biely text, radius približne 4px
- header: približne 95px, transparentný layout s logom, telefónom, emailom a menu
- homepage: 11 vykreslených Elementor sekcií

Toto je zachytenie existujúceho stavu, nie odporúčanie pre finálny redizajn.

## Stránky a Elementor štruktúra

| ID | Stránka | Stav | Elementor štruktúra |
|---:|---|---|---|
| 18048 | Domov | publish | 11 sections, 13 columns, 12 widgets; mapa, intro desktop/mobile, lokalita, galéria, mapa |
| 19924 | Byty | publish | 4 sections, 4 columns, 9 widgets; Image Map, headings, shortcode, property card |
| 19534 | Domy | publish | 1 section, 1 column, 5 widgets; property card v3 |
| 18787 | Galéria | publish | 3 sections, 3 columns, 2 widgets; slider, vizualizácie, image gallery |
| 16941 | Kontakt | publish | 5 sections, 7 columns, 9 widgets; text, formulár, Google Maps |
| 20049 | Partneri | publish | 3 sections, 4 columns, 10 widgets; obrázky, headings, text |
| 19170 | Referencie | publish | 5 sections, 9 columns, 15 widgets; referencie, headings, text, dividers |
| 18855 | Štandard | publish | 3 sections, 4 columns, 13 widgets; headings, text, technický štandard |
| 20311 | Pozemky | draft | 3 sections, 3 columns, 6 widgets; rozpracovaná property carousel verzia |
| 18808 | Byty koncept | draft | historický koncept |

Ďalšie publikované právne stránky: Zásady ochrany osobných údajov a Zásady používania súborov cookie.

## Menu baseline

- `main-menu`, `top-menu` a `mobile-menu-hed6` používajú menu ID 61 a obsahujú 8 položiek: Domov, Domy, Byty, Galéria, Referencie, Štandard, Partneri, Kontakt.
- `main-menu-left` používa menu ID 161: Domov, Domy, Byty, Galéria.
- `main-menu-right` používa menu ID 162: Referencie, Štandard, Partneri, Kontakt.
- Databázová položka Domov v menu ID 61 má historický URL `http://zelenemokrance.sk`; MU-plugin už obsahuje frontendovú normalizáciu na `home_url('/')`.

## TranslatePress audit

TranslatePress je nastavený iba na jeden jazyk:

- default language: `sk_SK`
- translation language: `sk_SK`
- ďalší verejný jazyk nie je nakonfigurovaný
- gettext tabuľka obsahuje 3 787 záznamov
- vyplnený preklad má 833 záznamov
- prázdnych je 2 954 záznamov
- automaticky schválený status `1` sa nepoužíva; existujú najmä statusy TranslatePress `4` a `0`

Najväčšie domény v tabuľke:

- `wordfence`: 1 632 záznamov, 0 preložených
- `elementor`: 484 záznamov, 386 preložených
- `houzez`: 286 záznamov, 7 preložených
- `houzez-theme-functionality`: 276 záznamov, 5 preložených
- `default`: 199 záznamov, 143 preložených
- `complianz-gdpr`: 185 záznamov, 132 preložených
- `contact-form-7`: 77 záznamov, 70 preložených

Demo Houzez frázy nemajú preklad v TranslatePress: `Compare listings`, `Compare`, `Login`, `Lost your password?`, `Reset Password` ani `User registration is disabled for demo purpose.`

### Odporúčanie pre preklady

Celých 833 prekladov sa nemá kopírovať do MU-pluginu. Preklady Elementor textov, formulárov a pluginových domén závisia od TranslatePress databázy a jeho runtime mechanizmu. Hardcoding celého exportu by bol krehký a pri zmene textu alebo pluginu by vznikali duplicity.

Odporúčaný postup:

1. ponechať TranslatePress počas obsahovej konsolidácie;
2. odstrániť alebo skryť demo UI cez MU-plugin, nie cez preklad;
3. po dokončení novej pozemkovej verzie exportovať iba schválené stabilné globálne UI frázy;
4. tie prípadne uložiť ako malú doménovo-scoped `gettext` mapu v samostatnom MU-plugin snippet-e;
5. až potom otestovať, či TranslatePress ešte prináša hodnotu, a prípadne ho deaktivovať.

## Bricks migrácia

Migrácia je technicky možná, ale nie ako jednoduché prepnutie témy. Bude treba nanovo vytvoriť:

- globálne farby, typografiu, spacing a header/footer;
- menu a štyri cieľové stránky Pozemky, Lokalita, Galéria, Kontakt;
- interaktívnu mapu parciel a stavy voľné/rezervované/predané;
- tabuľku parciel, PDF odkazy, CTA a formuláre;
- responzívne rozloženia a SEO/meta pravidlá;
- prípadné napojenie na Houzez property data alebo nový vlastný dátový model.

Elementor JSON sa môže použiť ako obsahová referencia, nie ako spoľahlivý import do Bricks. Pred migráciou treba rozhodnúť, či mapu, parcely a ceny necháme v Image Map Pro/Houzez, alebo ich postavíme na novom WordPress-native dátovom modeli.

## Stav snapshotu

- verejný frontend a štruktúra sú zdokumentované;
- vizuálny baseline je uložený v Novamira Design Library;
- preklady sú ponechané v TranslatePress databáze;
- Bricks build zatiaľ nezačal;
- MU-plugin hardening je uložený v repozitári, produkčný deploy ešte neprebehol.
