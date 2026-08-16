# ContentSeoMissing Semantics

`ProjectionSourceAssemblyStatus::ContentSeoMissing` est produit exclusivement lorsque `ContentSeoSourceSnapshotReader::readByListing()` retourne `Missing`.

Port : `Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotReader`.

Identité : `ContentSeo\Domain\ValueObject\ListingId`.

DTO attendu : `ContentSeoSourceDecision` dans `ContentSeoSnapshotReadResult::Found`.

Consumer immédiat : `CertifiedPublicListingProjectionSource`. En aval, `CertifiedPublicProjectionSourceLookup` réduit ce statut en `SourceUnavailable`, l’activation en `NotReady`, sans fallback.
