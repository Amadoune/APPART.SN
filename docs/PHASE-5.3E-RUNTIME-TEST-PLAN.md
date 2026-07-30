# Phase 5.3E — Runtime & Queue Test Plan

## Campagne ciblée

La campagne ciblée couvre :

- composition de `ModerationRuntimeV1` ;
- exposition exclusive des stores propriétaires ;
- disponibilité saine et fail-closed ;
- délégation Queue sans décision métier ;
- singletons et résolution Laravel uniques ;
- extension saine de Runtime Health ;
- interdictions architecturales du jalon.

Commande :

```text
php artisan test \
  tests/Unit/ModerationRuntime/ModerationRuntimeTest.php \
  tests/Feature/ModerationRuntimeCompositionTest.php \
  tests/Architecture/ModerationRuntimeArchitectureTest.php
```

Résultat terminal : `8 tests, 195 assertions — PASS`.

## PostgreSQL Queue ciblé

La preuve PostgreSQL couvre :

- projection locale ;
- claim ;
- lease expirée et reprise ;
- checkpoint monotone ;
- résolution réelle depuis la composition Runtime.

Commande :

```text
php vendor/bin/phpunit --configuration phpunit.postgresql.xml \
  tests/PostgreSQL/ModerationRuntime/PostgreSqlModerationRuntimeTest.php
```

Résultat terminal : `1 test, 7 assertions — PASS`.

## Architecture complète

Commande :

```text
php artisan test tests/Architecture
```

Résultat terminal : `659 tests, 51 994 assertions — PASS`.

## Qualité statique

- PHPStan : `0 erreur — PASS` ;
- Pint : `PASS` ;
- `git diff --check` : `PASS`.

## Critère terminal

Tout échec ciblé, dépendance externe, composant HTTP/Event/Delivery/Outbox ou
nouvelle migration impose `NO GO`.
