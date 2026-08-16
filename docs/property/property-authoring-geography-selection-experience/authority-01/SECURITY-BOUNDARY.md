# Security Boundary

## Query GET

- session IAM obligatoire ;
- aucun owner client ;
- allowlist stricte des quatre paramètres ;
- `PlaceType` fermé ;
- UUID canonique pour le parent ;
- limit 1..100, défaut 50 ;
- curseur opaque, taille bornée et intégrité vérifiée par F1 ;
- aucune interpolation SQL dans HTTP ;
- throttling authoring ;
- réponse minimale et `Cache-Control: no-store` dans l’expérience authentifiée.

## Save Authoring futur

- `ownerAccountId` provient exclusivement de la session ;
- `geographicPlaceId` est un UUID déclaré, jamais mass-assigné sans validation ;
- le contexte de preuve possède une allowlist séparée et n’est jamais persisté ;
- le serveur rejoue F1 et compare par égalité exacte ;
- `AddressIntentId`, `AddressId`, BusinessYear et champs techniques restent interdits au client ;
- les paramètres inconnus sont refusés.

## Menaces fermées

Un UUID deviné, un curseur d’un autre parent, un label injecté, un parent absent, une Place disabled/merged ou une réponse F1 indisponible ne peut produire une sauvegarde acceptée. Aucun message ne révèle l’existence d’une Place hors du scope demandé.
