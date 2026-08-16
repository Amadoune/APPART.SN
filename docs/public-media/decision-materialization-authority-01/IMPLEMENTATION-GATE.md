# Implementation gate

## Binary Delivery Implementation

GET `/media/{mediaId}/revisions/{assetVersion}`, sans session, eligibility owner-side, stream réel, MIME validé, `no-store`, 404 uniforme, aucune storageKey.

## Materialization Implementation après GO Delivery

`MaterializePublicMediaDecisionV2(ListingId)`, reader owner, payload V2, source vector, writer monotone, ListingPublished, refresh Media/asset et catch-up.

## Interdit

Migration, transformation, variant, Public Geography change, Projection/Search/ContentSeo write, ActiveGeneration et transaction distribuée.
