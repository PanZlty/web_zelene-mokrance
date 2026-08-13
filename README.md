# web_zelene-mokrance

Zdroj pravdy pre WordPress web https://www.zelenemokrance.sk/ a vlastny kod nasadzovany ako MU-plugin.

## Dokumentacia

- docs/WEB-AUDIT-2026-08-13.md
- docs/IMPLEMENTATION-BACKLOG.md
- .github/workflows/deploy-mu-plugins.yml

## Pravidla

- GitHub je zdroj pravdy pre vlastny PHP/CSS/JS kod.
- Neupravovat WordPress core, parent theme ani pluginove subory.
- Hesla, application passwords, SFTP udaje a API tokeny patria iba do GitHub Secrets alebo env premennych.
- Obsah, ceny, parcely, PDF dokumenty a pravne texty sa nesmu vymyslat.
- Zmeny v Elementor strankach sa robia cez zvoleny a zdokumentovany workflow.

## Deploy

Push do main pri zmene mu-plugins/** spusti GitHub Actions workflow.

GitHub Secrets: SFTP_HOST, SFTP_PORT, SFTP_USERNAME, SFTP_PASSWORD, SFTP_TARGET.

Cielova cesta v SFTP_TARGET musi byt potvrdena spravcom hostingu; do repozitara sa neuklada.