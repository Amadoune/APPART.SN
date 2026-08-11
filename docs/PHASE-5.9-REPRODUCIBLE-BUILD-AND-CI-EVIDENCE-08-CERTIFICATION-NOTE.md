# Reproducible Build & CI Evidence 08

Statut : `NO GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY`.

Source exclusive : nouveau clone du tag annoté immuable `phase-5.9-baseline-candidate-r5`, résolvant exactement vers `9801d9ed30ea3a5fa412708cd022d16bc84e472c`.

Le processus probatoire expose `C:\laragon\bin\git\usr\bin` dans son `PATH` avant Dependency Restore. Aucun clone, `vendor/`, `node_modules/`, build, package, manifeste, checksum, artefact ou PASS antérieur n'est réutilisé.

Campagne intégralement neuve et fail-fast. Aucune correction n'est autorisée.

## Portes franchies

- clone neuf / identité / ascendance / propreté : PASS ;
- Runtime et Composer épinglés : PASS ;
- Composer validate et install : PASS — 112 paquets ;
- npm ci et npm ls : PASS — 58 paquets ;
- Unit : PASS — 2 872 tests, 10 779 assertions ;
- Feature : PASS — 339 tests, 1 891 assertions ;
- Architecture : PASS — 911 tests, 85 843 assertions ;
- Foundation : PASS — 1 test, 4 assertions.

## Première porte non concluante

Porte : PostgreSQL complète.

Commande exacte :

`C:\laragon\bin\php\php-8.5.8-nts-Win32-vs17-x64\php.exe vendor/bin/phpunit --configuration phpunit.postgresql.xml`

Attendu : résultat terminal PASS de la campagne complète, 763 tests.

Obtenu : aucune sortie ni synthèse terminale ; timeout après 5 208 secondes (86 min 48 s), exit code `124`. Le processus PHP résiduel PID `16576` a été arrêté après le timeout pour préserver l'isolation.

Classification : `FAIL — NON TERMINAL / TIMEOUT`.

PHPStan, Pint, Frontend, Packaging A/B, comparaison, manifeste, checksums et clean-room sont `BLOCKED`. CI externe et reproduction indépendante sont `MISSING`.

Aucune correction ou relance n'est effectuée dans Evidence 08.

## Verdict

NO GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY
