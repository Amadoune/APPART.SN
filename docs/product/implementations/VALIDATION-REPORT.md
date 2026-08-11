# Property Authoring Public Surface 01 — Validation Report

## Résultats terminaux

| Campagne | Résultat |
|---|---|
| Unit + Feature + Architecture ciblés | PASS — 21 tests, 169 assertions |
| PostgreSQL Authoring ciblé | PASS — 5 tests, 29 assertions |
| PHPStan ciblé | PASS — 0 erreur |
| Pint ciblé | PASS |
| Régressions P02/P03/P04 ciblées | PASS — 16 tests, 96 assertions |
| `git diff --check` | PASS |

## Preuves fonctionnelles

- `propertyType=apartment` accepté et persisté ;
- `city=Dakar` acceptée et persistée ;
- `neighborhood=Almadies` accepté et persisté ;
- relecture owner-scoped restitue les trois valeurs ;
- type inconnu et localisations hors bornes rejetés en 422 ;
- champs inconnus et `accountId` fourni par le client restent refusés ;
- lignes historiques sans qualification toujours reconstructibles.

Aucun staging, commit ou tag n'a été réalisé.
