# Phase 5.2A — HTTP Boundary Discovery

Ce document définit une frontière future, pas des routes.

## Ressources candidates

- Property authoring : initier, consulter et modifier les faits autorisés ;
- Listing draft : créer, consulter, modifier, évaluer la complétude ;
- ownership : consulter, accorder ou révoquer une délégation locale ;
- portfolio : lister les Properties/Listings accessibles ;
- submission : demander le handoff vers Listing Publication.

## Règles obligatoires

- authentification par le middleware/session IAM gelé, sans modification ;
- auto-scope de l'AccountId ; tout AccountId de mutation fourni par le client
  est refusé ;
- `Idempotency-Key` UUID obligatoire pour chaque mutation ;
- version attendue obligatoire pour une mise à jour ;
- refus des champs inconnus ;
- résultats publics fermés, diagnostics internes séparés ;
- `Cache-Control: no-store` sur les ressources privées ;
- aucune adresse ou donnée privée dans une URL ;
- rate limiting par empreinte non-PII ;
- réponses `404/403` harmonisées contre l'énumération d'ownership.

## Séparation

Les adapters HTTP n'accèdent ni aux repositories ni à SQL. Ils appellent les
use cases authoring. Ils ne réutilisent pas les controllers F-01, F-02 ou IAM
et ne modifient aucune route gelée.
