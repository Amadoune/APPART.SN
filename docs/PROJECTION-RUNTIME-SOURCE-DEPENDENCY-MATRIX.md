# Matrice des dépendances Projection Runtime Source

| Donnée | Port propriétaire | Usage d'assemblage | Absence |
|---|---|---|---|
| Listing | `ListingRegistry` | Aggregate public source | blocage typé |
| Property | `PropertyRegistry` | Aggregate public source et place | blocage typé |
| ownership Media | `MediaCollectionOwnershipLookup` | identité officielle de collection | missing/ambiguous typé |
| MediaCollection | `MediaCollectionRegistry` | Aggregate public source | blocage typé |
| Search | `SearchDecisionReader` | version finale uniquement | missing/corrupted typé |
| Content/SEO | `ContentSeoSourceSnapshotReader` | sources SEO et historique déjà décidés | missing/corrupted typé |
| Geography | `PublicGeographyDecisionReader` | locality, breadcrumb et version | absence → readiness incomplète |
| Media public | `PublicMediaDecisionReader` | couverture désignée et version | absence → readiness incomplète |
| génération | `ActiveGenerationReader` | identité Active | missing/corrupted typé |
| temps | `DecisionTimeReader` | `decisionAt` original | missing/corrupted/divergent typé |

Aucun lecteur n'est remplacé ou contourné par l'orchestrateur.
