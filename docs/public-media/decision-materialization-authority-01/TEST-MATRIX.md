# Test matrix

## Completion 01 — matrice gouvernante

Binary Delivery : locator, GET 200 ready, MIME, no-store, cas invalides/révoqués 404, traversal et storageKey leak. Materialization : eligibility, ordre, primary, locator, source vector, version, replay/stale/divergent et empty SourceNotReady. Transport : ListingPublished et tous refresh Media/asset. PostgreSQL : Applied, Found/version positive, replay, mutations et catch-up.

## Unit (futur, après autorité préalable)

Source assembly, éligibilité, ordre, primary, URL resolver, payload, révision, Applied/AlreadyApplied/obsolete/divergent, empty/unavailable.

## Feature/transport

ListingPublished initial, mutations Media et delivery refresh, replay.

## PostgreSQL

Applied, reader Found/version positive, replay, add/remove/reorder/unavailable, catch-up real-like.

## Architecture

Owner Public Media, aucune dépendance Projection, aucun write Geography/ContentSeo/Search, aucune transaction distribuée, migration ou transformation binaire.
