# Phase 5.2C — Persistence Test Plan

## Unit ciblé

- sérialisation bijective des trois états ;
- conservation d’un objet JSON pour des contacts publics vides ;
- restauration des enums, dates, listes et maps.

## Architecture

- migration additive 061 ;
- absence de FK et cascade ;
- exactement trois stores propriétaires ;
- absence de dépendance IAM, Professional Status, Listing Lifecycle et Laravel ;
- absence de Provider, HTTP ou Runtime 5.2C.

## PostgreSQL ciblé

1. Profile : Applied, replay, divergence, révision append-only, lecture exacte.
2. Verification : historique de décisions et références opaques.
3. Portfolio : checkpoint monotone, rollback et absence d’écriture Listing.
4. Concurrence : un Applied et convergence déterministe sous advisory lock.

## Campagnes terminales

- Unit + Architecture ciblés ;
- Architecture complète ;
- Unit complète ;
- PostgreSQL 18.x ciblé ;
- PostgreSQL complet selon décision de certification ;
- PHPStan ;
- Pint ;
- `git diff --check`.
