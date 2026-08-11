# APPART.SN Product Amendment — Public Search Filterable Read Model 01

## Objectif

Qualifier puis matérialiser, si possible, les filtres publics `transaction`, `ville` et `propertyType` avant pagination dans la projection publique existante.

## Matrice des sources

| Champ | Source autoritative observée | État dans la projection publique | Décision |
|---|---|---|---|
| Transaction | `ListingDraftState::transactionKind`, persistance Authoring | absente de `Listing`, `SearchListingProjection` et `PublicListingReadModel` | BLOCKED |
| Ville | décision Public Geography et breadcrumb public | présente dans `SeoListingProjection`/breadcrumb, non exposée par le Summary | AVAILABLE_NOT_EXPOSED |
| Type | Aggregate Property → `SearchListingProjection::propertyType` | présente dans `PublicListingReadModel` et le Summary | AVAILABLE |
| Prix | `ListingDraftState::priceMinor`, persistance Authoring | absent de la projection publique | OUT_OF_SCOPE |
| Surface | Aggregate Property | présente dans `PublicListingReadModel`, non exposée par le Summary | AVAILABLE_NOT_EXPOSED |
| Pièces | Aggregate Property | présente dans `PublicListingReadModel`, non exposée par le Summary | AVAILABLE_NOT_EXPOSED |
| CanonicalPath | décision Content/SEO | présent et exposé | AVAILABLE |
| Headline | décision Content/SEO | présent et exposé | AVAILABLE |
| Image principale | décision Public Media | présente et exposée | AVAILABLE |

## Blocage d'autorité

La transaction obligatoire pour Acheter/Louer ne possède aucune représentation dans la projection publique actuelle. Sa seule source persistée identifiée est le draft Authoring. L'amendement interdit explicitement de relire Authoring, de joindre un Aggregate, de créer une autre projection ou d'inventer une valeur.

La décision Search utilisée pour P02 ne résout pas ce manque : elle porte uniquement une facette de type, cette facette n'est pas propagée dans le read model final et le catalogue `SearchFacetPolicy` ne définit aucune facette transaction.

## Conséquence

Étendre Query et HTTP avant de résoudre cette autorité créerait un paramètre `transaction` sans sémantique exécutable. Une implémentation partielle ville/type ne satisfait pas le critère de GO indivisible « Acheter et Louer fonctionnent ».

## Écart préalable nécessaire

Une décision d'autorité séparée doit qualifier le transport owner-scoped de la transaction depuis sa source existante vers la projection publique, sans lecture Authoring au moment de la recherche. Elle devra déterminer où cette donnée devient un fait public certifié et comment les projections existantes sont reconstruites de manière backward-compatible.

Ce futur chantier n'est pas ouvert par le présent document.
