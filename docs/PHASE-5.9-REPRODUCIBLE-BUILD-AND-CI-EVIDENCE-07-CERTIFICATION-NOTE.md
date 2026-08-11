# Reproducible Build & CI Evidence 07

Statut : `NO GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY`.

Source probatoire exclusive : clone neuf du tag annoté immuable `phase-5.9-baseline-candidate-r5`, résolvant vers `9801d9ed30ea3a5fa412708cd022d16bc84e472c`.

La campagne est intégralement neuve, fail-fast, sans recyclage d'artefact ni de résultat terminal des Evidence 04, 05 ou 06. Aucune correction n'est autorisée pendant ce jalon.

Ce document est une modification normative post-R5 et ne fait pas partie de la baseline candidate R5.

## Première divergence terminale

Porte : `Dependency Restore`.

Commande exacte :

`C:\laragon\bin\php\php-8.5.8-nts-Win32-vs17-x64\php.exe C:\laragon\bin\composer\composer.phar install --prefer-dist --no-interaction --no-progress`

Résultat : exit code `1`.

Attendu : installation terminale des 112 paquets verrouillés depuis le clone neuf R5.

Obtenu : Composer ne peut extraire les distributions, car l'extension PHP ZIP n'est pas chargée et `unzip`/`7z` sont indisponibles dans le `PATH` effectif du processus Evidence 07. La qualification ultérieure a démontré que `unzip.exe` existait physiquement sous `C:\laragon\bin\git\usr\bin`, sans être exposé au processus. Le fallback source commence, puis Composer s'arrête dans `ZipDownloader.php` sans restauration complète.

Impact : `npm ci`, Unit, Feature, Architecture, Foundation, PostgreSQL, PHPStan, Pint, Frontend, Packaging A/B, comparaison, manifeste, checksums, clean-room, CI externe et reproduction indépendante ne peuvent pas fournir de preuve terminale Evidence 07.

Aucune activation d'extension, correction ou substitution de dépendances n'est appliquée dans ce jalon.

## Verdict

NO GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY
