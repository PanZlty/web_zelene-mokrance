# Implementacny backlog

## P0

- [ ] Odstranit Compare listings, Compare, Login, Reset Password a demo registration text.
- [ ] Vypnut alebo odpojit Houzez compare/favorites/account moduly.
- [ ] Opravit menu URL na jednu HTTPS verziu domeny.
- [ ] Nastavit footer rok dynamicky a doplnit nazov, ICO a sidlo.
- [ ] Odstranit social ikony, ak nebudu udrziavane.
- [ ] Opravit chybajuci obrazok mapy a overit vsetky asset URL.

## P1 - hlavna ponuka pozemkov

- [ ] Vytvorit landing page /pozemky/.
- [ ] Zvolit a zdokumentovat Elementor workflow pre novu stranku.
- [ ] Doplni tabulku parciel: parcela, m2, cena za m2, celkova cena, stav.
- [ ] Doplni interaktivnu mapu so stavmi volne, rezervovane, predane.
- [ ] Pridat cennik a situacny plan v PDF.
- [ ] Pridat siete a technicku pripravenost.
- [ ] Pridat Co je v cene: inzinieringu, poplatky a povolenia.
- [ ] Pridat regulativy a povolene podlaznosti po potvrdeni podkladov.
- [ ] Pridat proces kupy s casovym ramcom.
- [ ] Pridat lokalitu, dostupnost a Google mapu.
- [x] Pridat CTA Rezervovat obhliadku do mapy a tabulky dostupnych pozemkov.

## P1 - formular

- [ ] Upravit CF7 formular ID 20171.
- [ ] Doplni vyber parcely, telefon, preferovany kontakt a obhliadku.
- [ ] Doplni GDPR checkbox s pravne schvalenym znenim.
- [ ] Overi recipient, Reply-To a dorucitelnost cez WP Mail SMTP.

## P2 - vizual a vykon

- [ ] Galeria: ponechat 2 az 3 rendery a realne fotografie/video.
- [ ] Zachovat dronove fotografie lokality.
- [ ] Prekonvertovat stare JPG/PNG na WebP alebo AVIF.
- [ ] Nastavit srcset, rozmery, lazy-load mimo prveho viewportu.
- [ ] Doplni alt texty.
- [ ] Po deployi znovu spustit Lighthouse desktop aj mobile.

## P2 - URL a SEO

- [ ] Rozhodnut redirect alebo noindex pre stare stranky Domy, Byty, Standard, Referencie, Partneri.
- [ ] Zlucit Referencie a Partneri do bloku O nas.
- [ ] Premenovat Standard na Technicka pripravenost pozemkov po obsahovej nahrade.
- [ ] Doplni meta description, Open Graph, canonical a 404 monitor.

## Akceptacne testy

- [ ] Desktop a mobile menu maju presne 4 polozky.
- [ ] Frontend neobsahuje Compare, Login ani demo registration.
- [ ] Interne odkazy nepouzivaju HTTP.
- [ ] /pozemky/ je 200 a ma tabulku, mapu, PDF a CTA.
- [ ] Formular ma parcelu, telefon a GDPR checkbox; testovaci email dorazi.
- [ ] Galeria obsahuje iba schvaleny vyber a obrazky vracaju 200.
- [ ] Footer obsahuje aktualny rok, nazov, ICO a sidlo.
