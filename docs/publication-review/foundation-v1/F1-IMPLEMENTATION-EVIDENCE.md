# F1 — Implementation Evidence

## Code

La nouvelle capacité est isolée sous `src/Modules/PublicationReview` :

- contrats Application et modèles readonly ;
- consumer sans framework ;
- adapter PostgreSQL contenant seul le SQL ;
- schéma owner-scoped `publication_review` ;
- provider de composition dédié.

## Persistance 096

`queue_items` conserve l'identité de l'événement, son checksum SHA-256, le Listing, la version de soumission, l'état, la version optimiste et les données de claim.

`command_ledger` conserve l'identité UUID de commande, son checksum, l'item, le résultat, la version résultante et l'instant enregistré.

Le rollback supprime exclusivement le schéma owner-scoped PublicationReview.

## Garanties démontrées

- double ingestion identique → `AlreadyApplied` ;
- identité identique et checksum divergent → `DivergentMessage` ;
- claim rejoué avec même commande → `AlreadyApplied` ;
- commande divergente → `DivergentCommand` ;
- version obsolète → `VersionConflict` ;
- claim concurrent protégé par advisory lock et row lock ;
- rollback d'une transaction externe préservé par savepoint ;
- aucun SQL dans Application ;
- consumer Projection indépendant.

## Gouvernance

Aucun staging, commit ou tag. BeginReview, ApprovePublication et P08 ne sont pas ouverts.
