# Graphe de dépendances

## Graphe exécutable actuel

```text
Listing Published ─┬─> ListingRegistry ───────────────┐
                   ├─> PropertyRegistry ──────────────┤
                   ├─> Media registries ──────────────┤
                   └─> [aucun producteur]             │
                              X                       │
search_discovery.public_search_decisions              │
             └─ SearchDecisionReader ─────────────────┤
                                                     v
                         CertifiedPublicListingProjectionSource
                                      └─ SearchMissing

public_projection.listing_projections ──> PublicSearchResultsReaderV1
```

## Graphe normatif cible déjà compatible

```text
Published facts (Listing + Property + Media)
        └─> SearchDiscovery materializer
              └─> SearchDecision + version
                    └─> Projection Source Assembly
                          └─> Public Projection Store
                                ├─> Public Listing
                                └─> Public Search Results
```

Le cycle apparaît seulement si le futur matérialiseur Search tente de lire le Projection Store. Cette option est interdite par l'ownership historique : ses entrées doivent être les faits publics versionnés Listing/Property/Media.
