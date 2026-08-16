# Contrat de la source Search

## Namespace et couche

- contrat Application : `Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionReader` ;
- DTO : `SearchDecisionReadResult` ;
- statut fermé : `Found`, `Missing`, `Corrupted` ;
- méthode unique : `readByListing(ListingId): SearchDecisionReadResult` ;
- décision : `SearchDecision(decisionId, listingId, version, projection)`.

`SearchProjection` contient `ProjectionState`, `SearchRank`, une liste ordonnée de `SearchFacet` et un `SourceRevisionSet` Listing/Property/Media.

## Implémentation et binding

`PostgreSqlSearchDecisionReader` lit exactement une ligne de `search_discovery.public_search_decisions` par `listing_id`. `SearchDecisionMapper` réhydrate le payload JSON, valide les value objects puis recalcule son SHA-256. Toute invalidité devient `Corrupted`.

Le binding productif est déclaré dans `PublicProjectionRuntimeServiceProvider`. Il n'est ni absent ni fail-closed artificiel.

## Écriture

`SearchDecisionWriter` et `PostgreSqlSearchDecisionWriter` existent avec `Applied`, `AlreadyApplied`, `RejectedObsolete`, `Divergent`. L'écriture est monotone et transactionnelle. Aucun consumer productif de `Published` n'appelle ce writer dans le repository actuel.
