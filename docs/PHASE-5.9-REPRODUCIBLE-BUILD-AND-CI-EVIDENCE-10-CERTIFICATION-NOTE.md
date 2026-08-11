# Reproducible Build & CI Evidence 10

Statut : `NO GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY`.

## Source certifiée

- clone neuf du tag annoté `phase-5.9-baseline-candidate-r5` ;
- résolution exacte : `9801d9ed30ea3a5fa412708cd022d16bc84e472c` ;
- worktree source propre avant, pendant et après les packagings ;
- aucune modification, aucun commit, aucun tag et aucune matérialisation R6.

## Preuves terminales acquises

- Runtime : PHP 8.5.8, Composer 2.9.4, Node.js 24.17.0, npm 11.13.0 ;
- Composer validate et restauration : PASS, 112 packages ;
- npm restore : PASS, 58 packages ;
- Unit : PASS, 2 872 tests, 10 779 assertions ;
- Feature : PASS, 339 tests, 1 891 assertions ;
- Architecture : PASS, 911 tests, 85 843 assertions ;
- Foundation : PASS, 1 test, 4 assertions ;
- PostgreSQL : PASS, 763 tests, 3 623 assertions, exit code 0 ;
- PHPStan : PASS, 0 erreur ;
- Pint global : PASS ;
- Frontend : PASS ;
- Packaging A : PASS, exit code 0, 9 522 fichiers ;
- Packaging B : PASS, exit code 0, 9 522 fichiers ;
- SHA-256 archive A/B identique : `d6f301796390b0c7da15fe19cb6fe8de0f53468e929645a310770924bec5cb3f` ;
- SHA-256 arbre A/B identique : `e19435b7bf7ed66f19dfdd7d3e33d0a5c937cefb22baab12dc42b6b6350514ab` ;
- manifestes complets, cohérents avec R5 et identiques ;
- clean-room : PASS, campagne exécutée depuis un répertoire vide et un clone neuf R5.

## Première divergence terminale

`CI externe` : `MISSING`.

Le dépôt local ne possède aucun remote Git configuré et aucun exécuteur GitHub CLI n'est disponible. Aucun run externe rattachable à R5 ne peut donc être observé ou déclenché dans le périmètre autorisé. La présence locale du workflow ne constitue pas une preuve d'exécution externe.

Par fail-fast, la reproduction indépendante est `BLOCKED`. Aucun PASS historique ou artefact antérieur n'est recyclé.

## Verdict

`NO GO PROPOSÉ — PHASE-5.9-REPRODUCIBLE-BUILD-AND-CI-EVIDENCE-10`

La campagne est close et historisée. La qualification de l'environnement CI externe constitue l'unique jalon 5.9 actif ; Evidence 11 reste non ouverte.
