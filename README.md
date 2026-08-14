# web_zelene-mokrance

Zdroj pravdy pre WordPress web https://www.zelenemokrance.sk/ a vlastny kod nasadzovany ako MU-plugin.

## Dokumentacia

- docs/WEB-AUDIT-2026-08-13.md
- docs/CURRENT-SITE-SNAPSHOT-2026-08-13.md
- docs/CURRENT-SITE-SNAPSHOT-2026-08-14.md
- docs/CURRENT-SITE-TEXTS-2026-08-13.md
- docs/CURRENT-SITE-TEXTS-2026-08-13.json
- docs/POZEMKY-DATA-MODEL.md
- docs/FORMULAR-OBHLIADKY.md
- docs/IMPLEMENTATION-BACKLOG.md
- mu-plugins/README.md

## Pravidla

- GitHub je zdroj pravdy pre vlastny PHP/CSS/JS kod.
- Neupravovat WordPress core, parent theme ani pluginove subory.
- Hesla, application passwords, SFTP udaje a API tokeny patria iba do GitHub Secrets alebo env premennych.
- Obsah, ceny, parcely, PDF dokumenty a pravne texty sa nesmu vymyslat.
- Zmeny v Elementor strankach sa robia cez zvoleny a zdokumentovany workflow.

## Deploy

Automaticky deploy workflow pre MU-plugin je definovany v `.github/workflows/deploy-mu-plugins.yml`. Push zmeny v `mu-plugins/**` do vetvy `main` spusti kontrolu PHP syntaxe a nasledny SFTP upload.

Deploy vyzaduje nastavene GitHub Secrets: `SFTP_HOST`, `SFTP_PORT`, `SFTP_USERNAME`, `SFTP_PASSWORD`, `SFTP_TARGET`. Presna cielova cesta ani pristupove udaje sa neukladaju do repozitara.
