# Dependency Restore Environment Qualification 01

Statut : `GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY`.

Objectif exclusif : qualifier et, si nécessaire, corriger au niveau environnemental les prérequis de la commande officielle Composer `install --prefer-dist --no-interaction --no-progress` depuis un clone neuf de R5.

R5, ses fichiers versionnés, son commit et son tag restent strictement immuables. Evidence 08 est identifiée et non ouverte.

## Preuves terminales

- PHP CLI : `C:\laragon\bin\php\php-8.5.8-nts-Win32-vs17-x64\php.exe` ;
- `php.ini` chargé : `C:\laragon\bin\php\php-8.5.8-nts-Win32-vs17-x64\php.ini` ;
- ZIP PHP : non chargé ; `php_zip.dll` est présent mais aucune activation n'est nécessaire ;
- extracteur qualifié : `C:\laragon\bin\git\usr\bin\unzip.exe` ;
- correction : ajout de ce répertoire au `PATH` du seul processus probatoire ;
- Git : disponible via `C:\Program Files\Git\cmd\git.exe` ;
- Composer : 2.9.4, SHA-256 `d3eb5c4cb2e708267dac5f9a76d2a57b07836c7ea68783a1a02bd6d94753ea80` ;
- clone neuf R5 initialement propre : PASS ;
- `composer validate --strict` : PASS ;
- `composer install --prefer-dist --no-interaction --no-progress` : PASS — 112 paquets, exit code 0 ;
- statut Git après restauration : propre, hors fichiers générés et ignorés ;
- R5 et son tag : inchangés.

La divergence était exclusivement environnementale. Aucun fichier versionné de R5 ne doit être modifié et aucune R6 n'est nécessaire.

## Verdict

GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY
