# Property Authoring Public Surface 01 — Implementation Evidence

## Chaîne matérialisée

`Session IAM → Request stricte → Property Authoring HTTP Runtime → PropertyAuthoringStore → PostgreSQL → GET owner-scoped`

## Changements techniques

- extension rétrocompatible de `PropertyAuthoringState` ;
- mapping bidirectionnel des trois champs ;
- écriture initiale et mise à jour avec conservation des valeurs non modifiées ;
- relecture HTTP des trois champs ;
- validation stricte sur les deux surfaces Authoring existantes ;
- migration additive 094 et rollback associé ;
- intégration de 094 à l'environnement PostgreSQL ciblé.

## Garanties conservées

- owner scope issu de la session, jamais du payload ;
- advisory lock transactionnel par `propertyId` ;
- optimistic locking et idempotence existants ;
- checksum de l'intention incluant les nouvelles valeurs ;
- absence de dépendance Media, Listing Lifecycle, Search ou Projection ;
- aucune création ou mutation d'Aggregate `Property` ;
- absence de valeur rétroactive pour les états historiques.

## Démonstration probatoire

La preuve ciblée crée l'état `apartment / Dakar / Almadies`, le persiste dans PostgreSQL puis le relit depuis un nouvel appel au store. Les trois valeurs reconstruites sont strictement identiques.
