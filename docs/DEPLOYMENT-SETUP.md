# Deployment setup blocker

GitHub Actions deploy workflow nebol vytvorený, pretože pred produkčným SFTP deployom treba potvrdiť presný remote target a rozsah súborov.

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
2. V repozitári najprv vytvoriť a skontrolovať MU-plugin kód.
3. Spustiť syntax check a review diffu.
4. Pridať deploy workflow s uploadom iba mu-plugins/.
5. Spustiť manuálny deploy na schválenom targete.
6. Overiť frontend, logy a rollback postup.

Do tohto dokumentu ani repozitára nepatria heslá ani application passwords.