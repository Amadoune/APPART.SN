# F4-A — Certification Note

## Verdict

**GO PROPOSÉ — F4-A PROPERTY AUTHORING GEOGRAPHY SELECTION EXPERIENCE AUTHORITY 01.**

## Décisions certifiées

- route unique : `GET /api/authoring/geography/selections` ;
- session IAM et throttling authoring, GET sans CSRF ;
- F1 comme source unique ;
- query limitée à type, parent, cursor, limit ;
- mapping fermé 422/200/404/500/503 ;
- DTO minimal ;
- navigation suivant uniquement les branches réelles ;
- choix explicite d’un item F1 ;
- validation save par rejeu exact de la page F1 et présence de l’ID ;
- revalidation Promotion contre `GeographicPlaceCatalog` ;
- dépréciation de city/neighborhood comme autorités ;
- handoff F4 complet.

Aucun choix requis par l’implémentation ne reste implicite. Aucun SQL direct, lookup Projection, PlaceId fabriqué ou confiance dans le navigateur n’est autorisé.

## Gouvernance

Aucun PHP, route, Controller, Blade, JavaScript, migration, State ou test n’a été modifié dans cette Authority. F4 reste suspendue. F4-A Implementation, F5, F6, F7 et RC2 Iteration 11 restent non ouvertes. Aucun staging, commit ou tag.
