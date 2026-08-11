# F2 — Validation Report

| Gate | Résultat terminal | Preuve |
|---|---|---|
| Unit ciblé | PASS | 3 tests, 25 assertions |
| Feature ciblée | PASS | 31 tests, 152 assertions |
| Architecture ciblée | PASS | 4 tests, 129 assertions |
| PostgreSQL ciblé | PASS | 1 test, 22 assertions |
| PHPStan ciblé | PASS | 0 erreur |
| Pint ciblé | PASS | aucun écart terminal |
| `git diff --check` | PASS | aucune erreur whitespace |

## Scénarios couverts

- Login réussi ;
- credential incorrect ;
- compte absent ;
- anti-énumération publique ;
- Session valide ;
- Session expirée ;
- Session révoquée ;
- Rotation et invalidation immédiate de l'ancien secret ;
- Logout et refus après reload ;
- replay idempotent ;
- limite de concurrence à cinq Sessions ;
- transport exclusif d'`AuthenticatedSessionContext(AccountId, SessionId)` ;
- composition Laravel singleton réelle ;
- non-régression des surfaces protégées IAM, Authoring, Media, Moderation et Professionals.

Aucun staging, commit ou tag n'a été effectué.
