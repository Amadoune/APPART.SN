# Certification — Legacy Migration Outbox Foundation

## Garanties transactionnelles

- journal owner-scoped append-only et un message par Delivery ;
- messageId, JSON et checksum SHA-256 canoniques ;
- Applied, AlreadyApplied, DivergentMessage et DependencyUnavailable ;
- lecture ordonnée et reconstruction UTC canonique ;
- retry borné à dix ;
- concurrence par clé primaire, `ON CONFLICT` et `FOR UPDATE` ;
- savepoints locaux et rollback externe préservé ;
- migration additive 085 et rollback, migration 084 inchangée.

## Campagnes

- Unit + Architecture : 29 tests, 191 assertions, succès ;
- PostgreSQL ciblé : 2 tests, 22 assertions, succès ;
- PHPStan ciblé : succès, zéro erreur ;
- Pint : succès ;
- `git diff --check` : succès ;
- aucune Feature HTTP et aucun test Transport.
