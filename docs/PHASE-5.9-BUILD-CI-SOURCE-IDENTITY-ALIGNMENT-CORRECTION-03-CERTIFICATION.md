# Build/CI Source Identity Alignment Correction 03

Statut : `GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY`.

## Objet certifié

Les trois contrôles d'identité Build/CI sont alignés sans wildcard, fallback ni auto-référence :

- source ancestrale immuable : R4, commit `058719f8aa154466056299b8c26bd7d51f944127` ;
- identité de la future candidate : tag annoté exact `phase-5.9-baseline-candidate-r5` ;
- surfaces alignées : `build/runtime.lock.json`, workflow CI et script de packaging ;
- test Architecture d'identité synchronisé et rejet explicite des anciennes références actives R3.

La candidate R5 n'est ni matérialisée ni taguée dans ce jalon. Materialization 04 et Evidence 07 restent non ouvertes.

## Preuves terminales

| Gate | Résultat |
|---|---|
| Identité ciblée | PASS — 3 tests, 27 assertions |
| Unit complète | PASS — 2 872 tests, 10 779 assertions |
| Feature complète | PASS — 339 tests, 1 891 assertions |
| Architecture complète | PASS — 911 tests, 85 843 assertions |
| Foundation | PASS — 1 test, 4 assertions |
| PostgreSQL complète | PASS — 763 tests, 3 623 assertions, 0 erreur, exit code 0 |
| PHPStan global | PASS — 0 erreur |
| Pint global | PASS |
| Frontend | PASS |
| `git diff --check` | PASS |
| Scan ciblé de secrets | PASS |

## Intégrité

R1, R2, R3 et R4, leurs commits et tags, les migrations 090–091, leurs rollbacks et les lockfiles restent immuables. Aucun staging, commit ou tag R5 n'est créé.

## Verdict

GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY
