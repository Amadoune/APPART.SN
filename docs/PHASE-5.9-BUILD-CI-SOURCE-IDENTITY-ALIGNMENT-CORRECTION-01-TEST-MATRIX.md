# Identity Test Matrix

| Preuve | Attendu | Statut |
|---|---|---|
| Candidate R3 correctement taguée | acceptée | BLOCKED — R3 non matérialisée après échec PostgreSQL global |
| Checkout R1 avec politique R3 | refusé | BLOCKED |
| mauvais commit/tag | refusé | BLOCKED |
| tag R3 déplacé/divergent | refusé | BLOCKED |
| workflow et packaging | même source R2, même tag R3, même contrôle exact | PASS — Architecture ciblée, 3 tests/25 assertions |
| wildcard/fallback | absent | PASS — Architecture ciblée |

## Gates globales

| Gate | Résultat |
|---|---|
| Unit | PASS — 2 872 tests, 10 779 assertions |
| Feature | PASS — 339 tests, 1 891 assertions |
| Architecture | PASS — 911 tests, 85 841 assertions |
| Foundation | PASS — 1 test, 4 assertions |
| PostgreSQL | FAIL — 763 tests, 759 PASS, 3 586 assertions, 4 erreurs, 916,083 s |
| PHPStan, Pint, frontend | BLOCKED — fail-fast |
| Commit/tag R3 et clean-room | BLOCKED — non exécutés |

Les quatre erreurs PostgreSQL proviennent du nettoyage OwnerSource 090 : la suppression du schéma `experience_acceptance` est refusée parce que les tables Outbox 091 en dépendent encore. Ce défaut n'est pas corrigé dans l'amendement d'identité.
