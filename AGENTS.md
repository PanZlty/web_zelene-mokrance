# AI agent instructions

Tento repozitar je zdroj pravdy pre vlastny kod a dokumentaciu webu zelenemokrance.sk.

## Workflow

1. Pred zmenou precitaj README, audit a backlog.
2. Vlastny PHP/CSS/JS kod patri do mu-plugins/.
3. Neupravuj WordPress core, Houzez theme ani pluginove subory.
4. Pouzi ABSPATH guard a idempotentne hooky.
5. Nevymyslaj parcelne data, ceny, siete, PDF, pravne texty ani casove ramce.
6. Nikdy necommituj credentials, application passwords, SFTP udaje, API tokeny ani .env.
7. Pred produkcnym deployom over PHP syntax a presny diff.
8. Nemen deploy target ani nepridavaj vzdialene mazanie bez explicitneho schvalenia.

## Visual work

Aktivny web pouziva Elementor. Pred prestavbou stranky treba zvolit a zdokumentovat workflow: Elementor, Gutenberg, theme template alebo kodovany MU-plugin. V ramci jednej stranky nemiesaj builder storage s raw post content.

## Deploy

Workflow je .github/workflows/deploy-mu-plugins.yml. Nasadzuje iba mu-plugins/ cez SFTP. Credentials su iba v GitHub Secrets.