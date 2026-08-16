# Implementation Boundary

## État

`PUBLIC SEARCH DECISION MATERIALIZATION IMPLEMENTATION 01` reste **NON OUVERTE**.

## Périmètre candidat après autorité préalable GO

- `MaterializePublicSearchDecisionV1` ;
- adapters/assembler SearchDiscovery pour Listing, Property et Media ;
- consumer du handoff `ListingPublished` ;
- opération catch-up utilisant le même use case ;
- provider et bindings nominatifs ;
- tests unitaires, architecture et PostgreSQL ciblés.

## Exclus

- HTTP/UX Search ;
- lecture de `public_projection.listing_projections` ;
- modification du writer ou de Projection ;
- transaction distribuée ;
- règle rank/facets inventée dans l'implémentation.

L'Implementation ne pourra être ouverte qu'après `Public Search Ranking and Facet Source Authority 01` GO.

## Completion 01

La condition préalable est désormais satisfaite par `PUBLIC SEARCH RANKING POLICY AUTHORITY 01` GO. Après certification de cette Completion, seule `PUBLIC SEARCH DECISION MATERIALIZATION IMPLEMENTATION 01` est autorisée, dans le périmètre détaillé par `IMPLEMENTATION-GATE.md`.

Search UX/API, mutation Public Projection, UI NotReady et toute transaction distribuée restent exclues.
