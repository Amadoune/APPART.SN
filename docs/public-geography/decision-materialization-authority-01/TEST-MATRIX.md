# Test matrix

Après levée du gate préalable :

- Unit : identité Place, payload canonique, séquence composite, replay, obsolete, divergent.
- PostgreSQL : Applied, reader Found/version positive, AlreadyApplied, évolution parent/feuille, catch-up.
- Architecture : owner Geography, absence d'accès Projection/Search/Media et de migration/transaction distribuée.
- Integration : Geography facts → décision Found → composante Geography readiness PASS.

Des tests d'URL Home, région, ville et de mutation parentale sont obligatoires.

## Completion 01

La V2 remplace les tests d'URL par l'assertion stricte `no URL`. Ajouter : vector vs watermark, RC2 root→leaf, rename parent/ancêtre, affected descendants, refresh delivery, pagination/replay, disable/merge, consumer V2 non navigable et absence de dépendance Projection/Media.

## Completion 02

Les tests source/refresh sont fermés. Les tests consumer non navigables (ContentSeo source, projection serialization, JSON-LD/read model et Blade sans lien Geography) restent interdits tant que l'autorité Consumer Breadcrumb Alignment n'a pas décidé leurs contrats V2.

## Completion 03 — matrice finale Implementation

- Unit : V2/no URL, identité RC2, vector/watermark, Available/Unavailable, replay/stale/divergent, rename ancêtre, disable/enable/merge.
- Feature/transport : ListingPublished initial, PlaceRenamed admission, mutation refresh consumer, pagination affected-terminal.
- PostgreSQL : Applied/Found/watermark positif/AlreadyApplied, mutation parent, disable/enable/merge, lookup JSONB, pagination, catch-up RC2-like.
- Architecture : owner Public Geography, aucune dépendance Projection/Media/Search/ContentSeo write, aucune transaction distribuée et aucune migration.

Les tests consumer V2 sont déjà certifiés et ne doivent pas être refaits hors régression.
