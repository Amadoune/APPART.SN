# Dependency Restore Environment Qualification 01 — Audit

L'audit couvre le PHP CLI, le `php.ini` chargé, l'extension ZIP, les extracteurs supportés, le `PATH`, Git et le Composer épinglé. La preuve ciblée sera exécutée dans un nouveau clone R5 sans réutiliser le `vendor/` incomplet d'Evidence 07.

Ce dossier est documentaire et postérieur à R5. Il ne fait pas partie de la baseline candidate immuable.

## Cause racine

Le runtime PHP 8.5.8 ne charge pas ZIP. L'extracteur `unzip.exe` existe déjà sous `C:\laragon\bin\git\usr\bin`, mais ce répertoire était absent du `PATH` de la première campagne. Composer ne pouvait donc extraire les distributions `--prefer-dist`.

## Correction minimale

Préfixer exclusivement le `PATH` du processus de qualification avec `C:\laragon\bin\git\usr\bin`. Aucun changement système persistant, aucune activation de module et aucune modification de R5.

## Résultat

Depuis un clone neuf distinct de celui d'Evidence 07, Composer valide le manifeste puis restaure les 112 paquets verrouillés avec exit code 0. `git status --short` reste vide après restauration, les répertoires générés étant ignorés.

Cette preuve ciblée qualifie l'environnement mais ne constitue pas une preuve terminale Evidence 08.
