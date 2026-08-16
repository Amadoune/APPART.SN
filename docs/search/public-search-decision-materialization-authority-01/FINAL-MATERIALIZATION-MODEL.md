# Final Materialization Model

## Entrées owners

| Source | Faits | Révision Search |
|---|---|---|
| ListingLifecycle | état et révision de publication | séquence positive, revisionId UUID, occurredAt |
| RealEstateCatalog | Aggregate Property et promotion autoritative | authoringVersion positive, commandId UUID, occurredAt du ledger de promotion |
| Media | collection, items et readiness | version positive de collection, MediaCollectionId UUID, lastChangedAt |

La révision Property est celle du fait de promotion ayant créé ou reconnu l'Aggregate compatible. L'Aggregate v0 n'est jamais transformé artificiellement en révision positive.

## Construction

1. relire les trois snapshots owner-scoped ;
2. construire les trois `SourceRevision` ;
3. dériver `ProjectionState` via `SearchVisibilityPolicy` ;
4. obtenir rang et facettes via la policy v1 ;
5. construire `SourceRevisionSet(listing, property, media)` ;
6. construire `SearchProjection(state, rank=0, facets=[], revisions)` ;
7. construire puis écrire `SearchDecision`.

Aucune lecture Public Projection.

## Visibilité exacte

- Listing `Terminal` ou Property `Archived` → `Removed` ;
- Listing différent de `Published`, Property différente de `Available` ou Media différente de `Ready` → `Hidden` ;
- Listing `Published` + Property `Available` + Media `Ready` → `Visible`.

Le source assembler traduit mécaniquement les faits owners vers les enums existants ; seul `SearchVisibilityPolicy` choisit le résultat.

## Chemin normal et absence de cycle

`Published → readers owners → visibility + ranking v1 → SourceRevisionSet → SearchProjection → SearchDecision → Writer → Reader Found → Projection Source Assembly → ProjectPublishedListingV1`.

Le matérialiseur ne lit jamais `public_projection.listing_projections`. Public Projection lit SearchDecision ; Public Search aval peut ensuite lire Public Projection. Le graphe reste acyclique.
