# Phase 5.2C — HTTP Test Plan

## Unit

- exhaustivité des cinq résultats mandat ;
- `ProfessionalId` absent hors `Resolved` ;
- exhaustivité des cinq décisions statut ;
- auto-scope `AccountId` → `ProfessionalId` ;
- reader F-05 jamais appelé sans mandat résolu ;
- convergence fail-closed des résolutions négatives.

## Feature

- session IAM requise sur les deux routes ;
- auto-scope provenant exclusivement de l'attribut IAM ;
- réponses et statuts fermés ;
- headers `no-store`, `no-cache` et `nosniff` ;
- aucune entrée `ProfessionalId`.

## Runtime

- résolution singleton de `ProfessionalEndpointRuntimeV1` ;
- résolution de ses deux dépendances publiques certifiées ;
- catalogue historique inchangé à 60 exigences ;
- extension `ProfessionalEndpoint` composée et `Healthy`.

## Architecture

- controller sans décision métier ;
- routes et provider uniques ;
- absence de SQL, persistence, Aggregate, Event, Delivery ou Outbox ;
- aucune migration ou modification de schéma ;
- aucune dépendance interdite.

## Campagnes terminales

- ciblée : 11 tests, 134 assertions — PASS ;
- Architecture : 652 tests, 51 080 assertions — PASS ;
- suite applicative : 2 877 tests, 59 504 assertions — PASS ;
- PHPStan, Pint et `git diff --check` — PASS.
