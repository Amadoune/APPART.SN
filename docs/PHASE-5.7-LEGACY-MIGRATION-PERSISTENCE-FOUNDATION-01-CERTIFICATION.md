# Certification — Legacy Migration Persistence Foundation

## Décision proposée

La Foundation matérialise exclusivement la source owner-scoped `LegacyMigration`, son mapper, son repository PostgreSQL et la migration additive 084.

## Preuves

- Unit + Architecture : 8 tests, 54 assertions, succès ;
- PostgreSQL ciblé : 2 tests, 10 assertions, succès ;
- PHPStan ciblé : succès, zéro erreur ;
- Pint ciblé : succès ;
- `git diff --check` : succès.

## Réserves de périmètre

Aucune Feature n'est exécutée. Aucune surface ultérieure n'est créée. Les contrôles autorisés sont concluants.
