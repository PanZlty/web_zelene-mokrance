# Formulár rezervácie obhliadky

Aktualizované: 13. augusta 2026

## Ako formulár funguje

Formulár obhliadky je implementovaný v MU-plugine `zelene-mokrance-image-map.php`. Nie je to formulár Bricks ani Contact Form 7.

1. Tlačidlo `Rezervovať obhliadku` na mape alebo v tabuľke otvorí spoločné modálne okno.
2. Formulár automaticky vloží ID vybraného pozemku.
3. Návštevník zadá meno, e-mail, telefón, správu a potvrdí GDPR súhlas.
4. JavaScript odošle formulár cez AJAX na WordPress `admin-ajax.php` s action `zm_plot_viewing`.
5. Server overí nonce, povinné polia, e-mail, ID pozemku a GDPR súhlas.
6. WordPress odošle správu funkciou `wp_mail()` na `varga@inforeal.sk` (hodnota čítaná z ACF options, fallback v kóde) a nastaví návštevníka ako `Reply-To`.

Tlačidlo v tabuľke aj v tooltipe mapy sa zobrazuje iba pri pozemkoch so stavom `available`. Rezervované a predané pozemky nemajú aktívne CTA. AJAX obsluha stav kontroluje znova na serveri a požiadavku pre nedostupný pozemok odmietne.

## SMTP a doručiteľnosť

Produkčný WordPress používa **FluentSMTP** (aktívny plugin). Odosielacia adresa zostáva na doméne zelenemokrance.sk; príjemcom správ je `varga@inforeal.sk`. Overenie doručiteľnosti a testovací e-mail sú otvorené v backlógu.

Konfigurácia musí obsahovať:

- overenú odosielaciu adresu na doméne `zelenemokrance.sk`;
- správne SPF, DKIM a podľa možností DMARC záznamy;
- vynútenú adresu odosielateľa z vlastnej domény;
- testovací e-mail a reálny test formulára;
- log neúspešných odoslaní bez ukladania SMTP hesla do GitHubu.

SMTP heslá a API kľúče nepatria do repozitára. Ukladajú sa iba v nastavení WordPressu, bezpečnom secrets úložisku alebo v serverových premenných.

## Akceptačný test

1. Otvoriť stránku s ponukou pozemkov.
2. Pri dostupnom pozemku kliknúť na `Rezervovať obhliadku` v tabuľke.
3. Overiť správne ID v nadpise a predvyplnenej správe.
4. Odoslať formulár s platnými údajmi a GDPR súhlasom.
5. Overiť úspešnú odpoveď na stránke a doručenie na `varga@inforeal.sk`.
6. Odpovedať na prijatý e-mail a overiť, že odpoveď smeruje na adresu návštevníka.
