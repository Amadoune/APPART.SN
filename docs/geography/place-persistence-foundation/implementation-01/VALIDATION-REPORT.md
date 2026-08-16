# Validation Report

| Campagne | Résultat |
|---|---|
| Unit + Architecture + Feature/Composition ciblés | PASS — 6 tests, 21 assertions |
| PostgreSQL ciblé | PASS — 5 tests, 17 assertions |
| Total | PASS — 11 tests, 38 assertions |
| PHPStan ciblé | PASS — 0 erreur |
| Pint ciblé | PASS |
| `git diff --check` | PASS |

PostgreSQL couvre migration, Repository réel, reconstruction, aliases, parent, merge, unicités, optimistic locking, rollback et réapplication. Une première exécution a identifié le binding PDO de `false`; le mapping a été corrigé en littéraux PostgreSQL déterministes puis toute la campagne finale a été rejouée avec succès.

Aucune campagne globale non impactée n'a été lancée.
