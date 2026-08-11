# Reproducible Build & CI Evidence 09

Statut : `NO GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY`.

Source exclusive : nouveau clone du tag annoté R5 `phase-5.9-baseline-candidate-r5`, résolvant exactement vers `9801d9ed30ea3a5fa412708cd022d16bc84e472c`.

Campagne neuve, intégrale, fail-fast et sans recyclage. Les longues gates sont explicitement supervisées, avec stdout/stderr temporaires hors dépôt et conservation de l'exit code enfant.

R5 reste immuable. R6 n'est ni ouverte ni matérialisée.

## Portes terminalement PASS

- clone / identité / propreté : PASS ;
- Runtime / Composer : PASS ;
- Dependency Restore Composer : PASS — 112 paquets ;
- Dependency Restore npm : PASS — 58 paquets ;
- Unit : PASS — 2 872 tests, 10 779 assertions ;
- Feature : PASS — 339 tests, 1 891 assertions ;
- Architecture : PASS — 911 tests, 85 843 assertions ;
- Foundation : PASS — 1 test, 4 assertions ;
- PostgreSQL : PASS — 763 tests, 3 623 assertions, exit 0, 1 532,638 s PHPUnit ;
- PHPStan : PASS — 0 erreur ;
- Pint global : PASS ;
- Frontend production : PASS ;
- Packaging A : PASS — exit 0, 976,308 s, 9 522 fichiers, manifeste complet.

## Première porte non PASS

Porte : Packaging B.

Commande :

`bash tools/release/build-release.sh dist/evidence09-b`

Attendu : second packaging terminal, indépendant de A, exit code 0.

Obtenu : exit code `1` après 1,027 seconde, avant génération. Le prérequis du script `test -z "$(git status --porcelain)"` observe `?? dist/`, créé par la sortie Packaging A sous `dist/evidence09-a`.

Classification : `FAIL — PROCESS EXECUTION / EVIDENCE PROCEDURE`.

Packaging A est valide, mais son emplacement non ignoré rend le clone non propre et interdit Packaging B. Aucun défaut fonctionnel, de génération A ou d'identité R5 n'est démontré. Aucune suppression, relocalisation ou relance n'est effectuée dans Evidence 09.

Comparaison A/B, manifeste final, checksums comparés et clean-room sont `BLOCKED`. CI externe réelle et reproduction indépendante sont `MISSING`.

## Verdict

NO GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY
