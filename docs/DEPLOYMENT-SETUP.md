# Deployment setup blocker

Workflow `.github/workflows/deploy-mu-plugins.yml` existuje a spúšťa sa na push do `main`. Kontrola PHP syntaxe prechádza, ale krok „Upload MU plugins to WordPress via SFTP" **zlyháva**. Treba overiť a opraviť GitHub Secrets a presnú cieľovú cestu.

## Potrebné nastavenie

GitHub Secrets:
- SFTP_HOST
- SFTP_PORT
- SFTP_USERNAME
- SFTP_PASSWORD
- SFTP_TARGET

SFTP_TARGET musí byť presná cesta na WordPress wp-content/mu-plugins pre zelenemokrance.sk. Cesta z iného projektu sa nesmie znovu použiť bez overenia.

## Bezpečný postup

1. Potvrdiť host, port, účet a presnú cestu.
2. Nastaviť/opraviť GitHub Secrets v repozitári.
3. Udržiavať MU-plugin kód v repozitári ako zdroj pravdy.
4. Spustiť syntax check a review diffu.
5. Spustiť manuálny deploy na schválenom targete.
6. Overiť frontend, logy a rollback postup.

## Ako to opraviť (kontrolný zoznam)

1. Otvoriť `Settings > Secrets and variables > Actions` v GitHub repozitári.
2. Nastaviť/opraviť secrets:
   - `SFTP_HOST` — hostiteľská doména/IP pre SFTP
   - `SFTP_PORT` — port (typicky 22; ak hostiteľ používa iný, presne ten)
   - `SFTP_USERNAME` — FTP/SFTP účet hostingu
   - `SFTP_PASSWORD` — heslo účtu
   - `SFTP_TARGET` — presná cesta k `wp-content/mu-plugins` (napr. `/domains/zelenemokrance.sk/www/wp-content/mu-plugins` — overiť u hostingu!)
3. V `.github/workflows/deploy-mu-plugins.yml` je teraz krok **Validate SFTP secrets**, ktorý pri chýbajúcom secrete zlyhá s jasnou hláškou (ktorý secret chýba).
4. Spustiť deploy: push do `main` alebo `workflow_dispatch`.
5. Overiť frontend a logy; ak SFTP zlyhá aj s nastavenými secrets, overiť u hostingu, či:
   - účet má oprávnenie písať do cieľovej cesty,
   - hosting neblokuje pripojenie z GitHub runnerov (IP allowlist),
   - cieľová cesta je správna.

Do tohto dokumentu ani repozitára nepatria heslá ani application passwords.