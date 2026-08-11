# F2 — Validation Report

Date : 2026-08-11

| Gate | Résultat | Preuve |
|---|---|---|
| Unit ciblé | PASS | délégation Begin/Approve mécanique |
| Feature ciblée | PASS | aliases vers le même singleton lazy |
| Architecture ciblée | PASS | aucune dépendance Property, Media, Projection, Search, Registry, PostgreSQL ou Infrastructure dans Application Review |
| Unit + Feature + Architecture | PASS | 6 tests, 186 assertions |
| PostgreSQL ciblé | PASS | 5 tests, 31 assertions |
| PHPStan ciblé | PASS | 0 erreur |
| Pint ciblé | PASS | formatage appliqué |
| `git diff --check` | PASS | aucune erreur |

Les scénarios PostgreSQL couvrent queue, claim, Begin, Approve, état terminal, replay, commande divergente, version obsolète et rollback externe. Les migrations 096–097 ne sont pas modifiées.
