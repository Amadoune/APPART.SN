# Implementation Gate

## Autorisé après GO

- policy v1 Domain/Application ;
- contrat et implémentation du matérialiseur ;
- readers/adapters owner-scoped et source assembler ;
- consumer du handoff Published ;
- opération de catch-up générique ;
- provider et bindings singleton/lazy ;
- tests Unit, PostgreSQL, Architecture et intégration ciblés.

## Bindings futurs

- `PublicSearchRankingPolicyV1` → implémentation pure v1, singleton ;
- `MaterializePublicSearchDecisionV1` → matérialiseur déterministe, singleton/lazy ;
- `ListingCatalog`, `PropertyCatalog`, `MediaCatalog` → adapters owner-scoped nominatifs ;
- `SearchDecisionReader` et `SearchDecisionWriter` → adapters PostgreSQL existants ;
- consumer `ListingPublished` et opération catch-up → même contrat matérialiseur.

Aucun binding Search UX/API.

## Interdit

- Search UX/API ;
- lecture ou écriture Public Projection depuis le matérialiseur ;
- modification Listing, Property ou Media ;
- transaction distribuée ;
- nouvelle table par confort ;
- correction UI NotReady ;
- constante rank dans l'orchestrateur.
