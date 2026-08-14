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

Do tohto dokumentu ani repozitára nepatria heslá ani application passwords.