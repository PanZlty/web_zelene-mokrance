# Implementacny backlog

Stav: 2026-08-14 — po Bricks migrácii a prvých opravách (Domov→Lokalita, footer rok, kontakty). `[x]` = hotové.

## P0

- [x] Odstranit Compare listings, Compare, Login, Reset Password a demo registration text. (frontend-hardening)
- [x] Vypnut alebo odpojit Houzez compare/favorites/account moduly. (Houzez nie je aktívny; UI skryté)
- [x] Opravit menu URL na jednu HTTPS verziu domeny.
- [x] Nastavit footer rok dynamicky. (Bricks `{do_action:zm_footer_year}` → 2026)
- [ ] Doplnit do footeru nazov, ICO a sidlo MOKRANCE INVEST družstvo. (caka na ICO/sidlo od majitela)
- [ ] Odstranit social ikony, ak nebudu udrziavane. (Instagram/Facebook zatial ostavaju)
- [ ] Opravit chybajuci obrazok mapy a overit vsetky asset URL.

## P1 - hlavna ponuka pozemkov

- [x] Vytvorit landing page /pozemky/.
- [x] Zvolit a zdokumentovat workflow. (Bricks native; AGENTS.md)
- [x] Doplnit tabulku parciel: parcela, m2, cena, stav. (shortcode `[zm_pozemky_table]`)
- [x] Doplnit interaktivnu mapu so stavmi volne, rezervovane, predane. (Image Map Pro + MU-plugin tooltipy)
- [ ] Pridat cennik a situacny plan v PDF. (caka na podklady)
- [x] Pridat siete a technicku pripravenost. (sekcia na /pozemky/)
- [x] Pridat Co je v cene / standard pozemku. (sekcia na /pozemky/)
- [ ] Pridat regulativy a povolene podlaznosti. (caka na potvrdenie podkladov)
- [x] Pridat proces kupy s casovym ramcom. (sekcia na /pozemky/)
- [x] Pridat lokalitu a dostupnost. (front page „Lokalita")
- [x] Pridat CTA Rezervovat obhliadku do mapy a tabulky dostupnych pozemkov.

## P1 - formular

- [x] Prepnut kontaktny formular na Bricks form. (emailToCustom: varga@inforeal.sk)
- [x] Doplnit vyber parcely, telefon a obhliadku. (modal obhliadky na /pozemky/)
- [x] Doplnit GDPR checkbox. (oba formulare)
- [x] Nastavit recipient a Reply-To. (varga@inforeal.sk cez ACF options)
- [ ] Overit dorucitelnost emailov. (FluentSMTP, SPF/DKIM, testovaci email)

## P2 - vizual a vykon

- [ ] Galeria: ponechat 2 az 3 rendery a realne fotografie/video.
- [ ] Zachovat dronove fotografie lokality.
- [ ] Prekonvertovat stare JPG/PNG na WebP alebo AVIF.
- [ ] Nastavit srcset, rozmery, lazy-load mimo prveho viewportu.
- [ ] Doplnit alt texty.
- [ ] Po deployi znovu spustit Lighthouse desktop aj mobile.

## P2 - URL a SEO

- [ ] Rozhodnut redirect alebo noindex pre stare stranky Domy, Byty, Standard, Referencie, Partneri.
- [x] Zlucit Referencie a Partneri do bloku O nas. (Kontakt stranka)
- [ ] Premenovat Standard na Technicka pripravenost pozemkov po obsahovej nahrade.
- [ ] Doplnit meta description, Open Graph, canonical a 404 monitor. (meta description na fronte stale „Pozemky a byty")

## Akceptacne testy

- [x] Desktop a mobile menu maju presne 4 polozky. (Lokalita, Pozemky, Galeria, Kontakt)
- [x] Frontend neobsahuje Compare, Login ani demo registration.
- [x] Interne odkazy nepouzivaju HTTP.
- [x] /pozemky/ je 200 a ma tabulku, mapu a CTA. (PDF este chyba)
- [x] Formular ma parcelu, telefon a GDPR checkbox.
- [ ] Galeria obsahuje iba schvaleny vyber a obrazky vracaju 200.
- [ ] Footer obsahuje aktualny rok, nazov, ICO a sidlo. (rok ano; ICO/sidlo caka)
